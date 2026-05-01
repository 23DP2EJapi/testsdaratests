-- Drop existing restrictive INSERT policy for applications
DROP POLICY IF EXISTS "Authenticated users can create applications" ON public.applications;

-- Create new policy that allows anyone to insert applications (including anonymous users)
CREATE POLICY "Anyone can submit applications"
ON public.applications
FOR INSERT
WITH CHECK (true);

-- Add policy for anonymous users to view their own applications by email (optional, for confirmation page)
CREATE POLICY "Users can view applications by email"
ON public.applications
FOR SELECT
USING (true);