-- 
-- PostgreSQL database dump
--

SET @statement_timeout
= 0;
SET @client_encoding
= 'UTF8';
SET @check_function_bodies
= false;
SET @client_min_messages
= warning;


SET @default_tablespace
= '';

SET @default_with_oids
= false;

--
-- Name: logins; Type: TABLE DATA; Schema: exo; Owner: MajorPetrov
--

CREATE TABLE membres
(
    pseudo character varying(25) NOT NULL,
    nom character varying(25) NOT NULL,
    presentation character varying(2048),
    password character varying(200) NOT NULL
);

CREATE TABLE messages
(
    id serial,
    pseudo character varying(25) NOT NULL,
    auteur character varying(25) NOT NULL,
    message character varying(140)
);

CREATE TABLE abonnements
(
    membre character varying(25) NOT NULL,
    abonne character varying(25) NOT NULL
);

CREATE TABLE avatars
(
    pseudo character varying(25) NOT NULL,
    type text,
    contenu bytea
);
