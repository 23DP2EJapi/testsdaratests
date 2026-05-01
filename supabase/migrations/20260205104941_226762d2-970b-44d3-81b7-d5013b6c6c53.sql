-- Create messages table for communication between listing owners and applicants
CREATE TABLE public.messages (
  id uuid NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  application_id uuid NOT NULL REFERENCES public.applications(id) ON DELETE CASCADE,
  sender_id uuid NOT NULL,
  content text NOT NULL,
  is_read boolean NOT NULL DEFAULT false,
  created_at timestamp with time zone NOT NULL DEFAULT now()
);

-- Create index for faster queries
CREATE INDEX idx_messages_application_id ON public.messages(application_id);
CREATE INDEX idx_messages_sender_id ON public.messages(sender_id);
CREATE INDEX idx_messages_created_at ON public.messages(created_at);

-- Enable Row Level Security
ALTER TABLE public.messages ENABLE ROW LEVEL SECURITY;

-- Enable realtime for messages
ALTER PUBLICATION supabase_realtime ADD TABLE public.messages;

-- Policy: Users can view messages for applications they're involved in
-- (either as listing owner or as applicant)
CREATE POLICY "Users can view their messages"
ON public.messages
FOR SELECT
USING (
  EXISTS (
    SELECT 1 FROM applications a
    JOIN listings l ON a.listing_id = l.id
    WHERE a.id = messages.application_id
    AND (a.user_id = auth.uid() OR l.user_id = auth.uid())
  )
);

-- Policy: Users can send messages if they're part of the conversation
CREATE POLICY "Users can send messages"
ON public.messages
FOR INSERT
WITH CHECK (
  auth.uid() = sender_id
  AND EXISTS (
    SELECT 1 FROM applications a
    JOIN listings l ON a.listing_id = l.id
    WHERE a.id = application_id
    AND (a.user_id = auth.uid() OR l.user_id = auth.uid())
  )
);

-- Policy: Users can mark messages as read
CREATE POLICY "Users can update message read status"
ON public.messages
FOR UPDATE
USING (
  EXISTS (
    SELECT 1 FROM applications a
    JOIN listings l ON a.listing_id = l.id
    WHERE a.id = messages.application_id
    AND (a.user_id = auth.uid() OR l.user_id = auth.uid())
  )
  AND sender_id != auth.uid()
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM applications a
    JOIN listings l ON a.listing_id = l.id
    WHERE a.id = messages.application_id
    AND (a.user_id = auth.uid() OR l.user_id = auth.uid())
  )
);