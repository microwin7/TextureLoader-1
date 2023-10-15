CREATE TABLE public.user_assets (
	uuid uuid NOT NULL,
	"name" varchar NOT NULL,
	hash varchar NOT NULL,
	metadata json NULL,
	CONSTRAINT user_assets_pk PRIMARY KEY (uuid, name)
);
CREATE INDEX user_assets_uuid_idx ON public.user_assets USING btree (uuid);
