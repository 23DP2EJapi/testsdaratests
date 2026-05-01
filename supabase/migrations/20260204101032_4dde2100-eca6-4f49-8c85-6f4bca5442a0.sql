-- Add user_id column to listings to track the creator
ALTER TABLE public.listings 
ADD COLUMN user_id uuid REFERENCES auth.users(id);

-- Allow authenticated users to insert their own listings
CREATE POLICY "Authenticated users can create listings"
ON public.listings
FOR INSERT
WITH CHECK (auth.uid() = user_id);

-- Allow users to update their own listings
CREATE POLICY "Users can update their own listings"
ON public.listings
FOR UPDATE
USING (auth.uid() = user_id);

-- Allow users to delete their own listings
CREATE POLICY "Users can delete their own listings"
ON public.listings
FOR DELETE
USING (auth.uid() = user_id);