ALTER TABLE public.reviews ADD COLUMN review_type text NOT NULL DEFAULT 'organization';
ALTER TABLE public.reviews ADD COLUMN reviewed_user_id uuid;