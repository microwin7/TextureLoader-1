CREATE TABLE public.user_assets (
	uuid uuid NOT NULL,
	"name" varchar NOT NULL,
	hash varchar NOT NULL,
	metadata json NULL,
	CONSTRAINT user_assets_pk PRIMARY KEY (uuid, name)
);
CREATE INDEX user_assets_uuid_idx ON public.user_assets USING btree (uuid);

CREATE TABLE public.user_assets_avatarcache (
	skinHash varchar NOT NULL,
	avatarHash varchar NOT NULL,
	scale int NOT NULL,
	CONSTRAINT user_assets_avatarcache_pk PRIMARY KEY (skinHash, scale)
);
CREATE INDEX user_assets_avatar_hash_idx ON public.user_assets_avatarcache USING btree (skinHash);