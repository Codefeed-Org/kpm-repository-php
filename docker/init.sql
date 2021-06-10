CREATE TABLE public.users (
	id serial NOT NULL,
	email varchar NOT NULL,
	"password" varchar NOT NULL,
	CONSTRAINT users_pk PRIMARY KEY (email)
);
CREATE UNIQUE INDEX users_id_idx ON public.users USING btree (id);