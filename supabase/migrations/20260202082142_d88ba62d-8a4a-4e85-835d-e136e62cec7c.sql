-- Drop overly permissive SELECT policy
DROP POLICY IF EXISTS "Users can view applications by email" ON public.applications;

-- Keep the original restrictive view policy for logged-in users
-- Existing policy "Users can view their own applications" already exists and is correct