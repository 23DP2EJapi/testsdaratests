
-- Drop FK from listings
ALTER TABLE public.listings DROP CONSTRAINT IF EXISTS listings_organization_id_fkey;
-- Drop organization_id column from listings
ALTER TABLE public.listings DROP COLUMN IF EXISTS organization_id;

-- Drop FK from reviews  
ALTER TABLE public.reviews DROP CONSTRAINT IF EXISTS reviews_organization_id_fkey;
-- Drop organization_id column from reviews
ALTER TABLE public.reviews DROP COLUMN IF EXISTS organization_id;

-- Drop the organizations table
DROP TABLE IF EXISTS public.organizations CASCADE;
