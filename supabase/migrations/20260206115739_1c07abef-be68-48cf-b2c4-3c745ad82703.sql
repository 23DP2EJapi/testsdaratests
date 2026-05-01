-- Drop the old policy that allows anyone to submit
DROP POLICY IF EXISTS "Anyone can submit applications" ON public.applications;

-- Create new policy requiring authentication
CREATE POLICY "Authenticated users can submit applications" 
ON public.applications 
FOR INSERT 
WITH CHECK (auth.uid() IS NOT NULL AND auth.uid() = user_id);