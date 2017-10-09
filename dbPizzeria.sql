DROP TABLE IF EXISTS pizza_ingrediente CASCADE;
DROP TABLE IF EXISTS ordine_pizza CASCADE;
DROP TABLE IF EXISTS pizze CASCADE;
DROP TABLE IF EXISTS ingredienti CASCADE;
DROP TABLE IF EXISTS ordini CASCADE;
DROP TABLE IF EXISTS utenti CASCADE;

CREATE TABLE pizze (
    id serial NOT NULL,
    nome text NOT NULL,
    prezzo numeric(4,2) NOT NULL,
    PRIMARY KEY (id),
    CHECK (prezzo > 0)
);

CREATE TABLE ingredienti (
    id serial NOT NULL,
    ingrediente text NOT NULL,
    quantita int DEFAULT 10,
    PRIMARY KEY (id),
    CHECK (quantita >= 0)
);

CREATE TABLE pizza_ingrediente (
    pizza int NOT NULL,
    ingrediente int NOT NULL,
    PRIMARY KEY (pizza, ingrediente)
);

CREATE TABLE utenti (
    id serial NOT NULL,
    nome text NOT NULL,
    cognome text NOT NULL,
    indirizzo text NOT NULL,
    telefono text,
    username text NOT NULL,
    password text NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE ordini (
    id serial NOT NULL,
    utente int NOT NULL,
    giorno date NOT NULL,
    ora time NOT NULL,
    indirizzo text NOT NULL,
    consegnato boolean DEFAULT FALSE,
    annullato boolean DEFAULT FALSE,
    PRIMARY KEY (id),
    CHECK (NOT(consegnato AND annullato))
);

CREATE TABLE ordine_pizza (
    id serial NOT NULL,
    ordine int NOT NULL,
    pizza int NOT NULL,
    quantita int NOT NULL,
    PRIMARY KEY (id),
    CHECK (quantita > 0)
);

ALTER TABLE pizza_ingrediente
ADD FOREIGN KEY (pizza) REFERENCES pizze
    ON DELETE CASCADE,
ADD FOREIGN KEY (ingrediente) REFERENCES ingredienti
    ON DELETE CASCADE;

ALTER TABLE ordini
ADD FOREIGN KEY (utente) REFERENCES utenti
    ON DELETE CASCADE;

ALTER TABLE ordine_pizza
ADD FOREIGN KEY (ordine) REFERENCES ordini
    ON DELETE CASCADE,
ADD FOREIGN KEY (pizza) REFERENCES pizze
    ON DELETE CASCADE;

/*FUNZIONI*/

/*funzione per creare un nuovo utente*/
CREATE OR REPLACE FUNCTION nuovo_utente(
    _username text,
    _password text,
    _nome text,
    _cognome text,
    _indirizzo text,
    _telefono text
) returns integer as $$
    INSERT INTO utenti (nome, cognome, indirizzo, telefono, username, password)
    VALUES (_nome, _cognome, _indirizzo, _telefono, _username, md5(_password))
    RETURNING id;
$$ language sql;

/*funzione per verificare le credenziali al login*/
CREATE OR REPLACE FUNCTION verifica_login(_usr text, _pwd text) returns integer as $$
DECLARE
    pwd_salvata text;
    id_user integer;
BEGIN
    SELECT password, id
    INTO pwd_salvata, id_user
    FROM utenti 
    WHERE username = _usr;
    IF md5(_pwd) = pwd_salvata
    THEN return id_user;
    ELSE return -1;
    END IF;
END;
$$ language plpgsql;

/*funzione che restituisce gli ingredienti di una pizza*/
CREATE OR REPLACE FUNCTION ingredienti_pizza(id_pizza int) returns table(id int, ingrediente text) as $$
    SELECT id, ingrediente
    FROM ingredienti
    WHERE id IN (
        SELECT ingrediente
        FROM pizza_ingrediente
        WHERE pizza = id_pizza
    );
$$ language sql;

/*funzione che ricerca tutte le pizze che contengono determinati ingredienti*/
CREATE OR REPLACE FUNCTION pizze_con_ingredienti(ingredienti int[]) returns table(id int, nome text, prezzo numeric(4,2)) as $$
    SELECT a.id, a.nome, a.prezzo 
    FROM
        pizze a INNER JOIN
        pizza_ingrediente b ON a.id = b.pizza
    WHERE 
        b.ingrediente IN (select * from unnest(ingredienti))
        OR array_length(ingredienti, 1) IS NULL
    GROUP BY a.id, a.nome, a.prezzo
    /*l'having finale permette di filtrare solo le pizze che contengono tutti gli ingredienti richiesti*/
    HAVING
        COUNT(*) = (SELECT * FROM array_length(ingredienti, 1))
        OR array_length(ingredienti, 1) IS NULL;
$$ language sql;

/*funzione per inserire un pizza nel listino e restituirne l'id*/
CREATE OR REPLACE FUNCTION aggiungi_pizza(_pizza text, _costo numeric(4,2)) returns int as $$
DECLARE
    return_id int;
BEGIN
   INSERT INTO pizze (id, nome, prezzo)
   VALUES (DEFAULT, _pizza, _costo)
   RETURNING id INTO return_id;

   RETURN return_id;
END;
$$ language plpgsql;

/*funzione per inserire un ingrediente al magazzino e restituirne l'id*/
CREATE OR REPLACE FUNCTION aggiungi_ingrediente(_ingrediente text) returns int as $$
DECLARE
    return_id int;
BEGIN
   INSERT INTO ingredienti (id, ingrediente)
   VALUES (DEFAULT, _ingrediente)
   RETURNING id INTO return_id;

   RETURN return_id;
END;
$$ language plpgsql;

/*funzione per aggiungere un ingrediente ad una pizza*/
CREATE OR REPLACE FUNCTION aggiungi_ingrediente_a_pizza(_pizza int, _ingrediente int) returns boolean as $$
DECLARE return_id int = -1;
BEGIN
    INSERT INTO pizza_ingrediente(pizza, ingrediente)
    SELECT _pizza, _ingrediente
    WHERE NOT EXISTS(
        SELECT *
        FROM pizza_ingrediente
        WHERE pizza=_pizza and ingrediente=_ingrediente
    )
    RETURNING pizza INTO return_id;
    IF return_id = -1
        THEN return false;
        ELSE return true;
    END IF;
END;
$$ language plpgsql;

CREATE OR REPLACE FUNCTION ordini_utente(_utente text) returns table(id int, nome text, cognome text, giorno date, ora time, indirizzo text, consegnato boolean) as $$
    SELECT a.id, b.nome, b.cognome, a.giorno, a.ora, a.indirizzo, a.consegnato
    FROM
        ordini a INNER JOIN
        utenti b ON a.utente = b.id
    WHERE 
        b.username = _utente
$$ language sql;

/*funzione per inserire un'ordinazione*/
CREATE OR REPLACE FUNCTION crea_ordine(_utente int, _giorno date, _orario time, _indirizzo text) returns int as $$
DECLARE
    return_id int;
BEGIN
   INSERT INTO ordini (utente, giorno, ora, indirizzo, consegnato, annullato)
   VALUES (_utente, _giorno, _orario, _indirizzo, false, false)
   RETURNING id INTO return_id;

   RETURN return_id;
END;
$$ language plpgsql;

/*funzione per aggiungere delle pizze ad un ordine*/
CREATE OR REPLACE FUNCTION aggiungi_pizze_a_ordine(_ordine int, _pizza int, _quantita int) returns boolean as $$
DECLARE return_id int = -1;
BEGIN
IF (EXISTS (SELECT * FROM ordine_pizza WHERE ordine = _ordine AND pizza = _pizza))
THEN    
    UPDATE ordine_pizza
    SET quantita = quantita + _quantita
    WHERE ordine = _ordine AND pizza = _pizza
    RETURNING ordine INTO return_id;
ELSE
    INSERT INTO ordine_pizza(ordine, pizza, quantita)
    VALUES (_ordine, _pizza, _quantita)
    RETURNING ordine INTO return_id;
END IF;
IF return_id = -1
    THEN return false;
    ELSE return true;
END IF;
END;
$$ language plpgsql;

CREATE OR REPLACE FUNCTION pizze_ordinabili() returns TABLE(id int, nome text, prezzo numeric(4,2)) as $$
    SELECT a.id, a.nome, a.prezzo 
    FROM
        pizze a INNER JOIN
        pizza_ingrediente b ON a.id = b.pizza INNER JOIN
        ingredienti c ON b.ingrediente = c.id
    GROUP BY a.id, a.nome, a.prezzo
    /*l'having finale permette di filtrare solo le pizze i cui ingredienti sono tutti presenti*/
    HAVING
        COUNT(*) = SUM(CASE WHEN c.quantita > 0 THEN 1 ELSE 0 END);
$$ language sql;

/*popola il database*/

SELECT nuovo_utente('administrator', 'administrator', 'admin', 'admin', 'pizzeria', NULL);

INSERT INTO ingredienti(ingrediente)
VALUES
    ('mozzarella'),
    ('mozzarella di bufala'),
    ('pomodoro'),
    ('origano'),
    ('basilico'),
    ('rosmarino'),
    ('grana'),
    ('pepe'),
    ('wurstel'),
    ('patate fritte'),
    ('prosciutto cotto'),
    ('prosciutto crudo'),
    ('bresaola'),
    ('salamino'),
    ('salsiccia'),
    ('rucola'),
    ('cipolla'),
    ('peperoni'),
    ('carciofi'),
    ('spinaci'),
    ('melanzane'),
    ('zucchine'),
    ('pomodorini freschi'),
    ('funghi'),
    ('gorgonzola'),
    ('stracchino'),
    ('scamorza'),
    ('ricotta'),
    ('capperi'),
    ('tonno'),
    ('acciughe'),
    ('olive'),
    ('aglio'),
    ('uovo'),
    ('ananas');

INSERT INTO pizze(nome, prezzo)
VALUES
    ('Margherita', 4.50),
    ('Viennese', 6.00),
    ('Patatosa', 6.50),
    ('Marinara', 3.00),
    ('Prosciutto e funghi', 6.00),
    ('4 stagioni', 6.50),
    ('Capricciosa', 6.50),
    ('Rustica', 6.60),
    ('Romana', 5.50),
    ('Strana', 8.00);

INSERT INTO pizza_ingrediente(pizza, ingrediente)
VALUES
    (1, 3), (1, 1),
    (2, 3), (2, 1), (2, 9),
    (3, 3), (3, 1), (3, 10),
    (4, 3), (4, 33), (4, 4),
    (5, 3), (5, 1), (5, 11), (5, 24),
    (6, 3), (6, 1), (6, 24), (6, 15), (6, 11), (6, 19),
    (7, 3), (7, 1), (7, 11), (7, 24), (7, 19), (7, 32),
    (8, 3), (8, 1), (8, 25), (8, 21),
    (9, 3), (9, 1), (9, 29), (9, 31), (9, 4), 
    (10, 3), (10, 1), (10, 31), (10, 33), (10, 35);

INSERT INTO ordini(utente, giorno, ora, indirizzo, consegnato)
VALUES
    (1, '10/09/2017', '20:30', 'via Filzi, 17', true),
    (1, '02/10/2017', '20:00', 'via Matteotti, 11', false);

INSERT INTO ordine_pizza(ordine, pizza, quantita)
VALUES
    (1, 5, 3),
    (1, 7, 1),
    (1, 9, 2),
    (2, 1, 6),
    (2, 2, 1);