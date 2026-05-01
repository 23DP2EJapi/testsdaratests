-- Allow listing owners to view applications for their listings
CREATE POLICY "Listing owners can view applications"
ON public.applications
FOR SELECT
USING (
  EXISTS (
    SELECT 1 FROM public.listings
    WHERE listings.id = applications.listing_id
    AND listings.user_id = auth.uid()
  )
);

-- Allow listing owners to update application status
CREATE POLICY "Listing owners can update application status"
ON public.applications
FOR UPDATE
USING (
  EXISTS (
    SELECT 1 FROM public.listings
    WHERE listings.id = applications.listing_id
    AND listings.user_id = auth.uid()
  )
);