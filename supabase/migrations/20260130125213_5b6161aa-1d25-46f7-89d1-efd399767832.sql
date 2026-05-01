-- Create organizations table
CREATE TABLE public.organizations (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  name TEXT NOT NULL,
  description TEXT,
  logo_url TEXT,
  website TEXT,
  email TEXT,
  phone TEXT,
  address TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Create listings table
CREATE TABLE public.listings (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  organization_id UUID REFERENCES public.organizations(id) ON DELETE CASCADE NOT NULL,
  location TEXT NOT NULL,
  category TEXT NOT NULL,
  time_commitment TEXT,
  spots INTEGER DEFAULT 1,
  requirements TEXT,
  benefits TEXT,
  is_urgent BOOLEAN DEFAULT false,
  is_new BOOLEAN DEFAULT true,
  is_online BOOLEAN DEFAULT false,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Create applications table
CREATE TABLE public.applications (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  listing_id UUID REFERENCES public.listings(id) ON DELETE CASCADE NOT NULL,
  user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE,
  full_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  motivation TEXT NOT NULL,
  cv_url TEXT,
  status TEXT NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Create reviews table
CREATE TABLE public.reviews (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  organization_id UUID REFERENCES public.organizations(id) ON DELETE CASCADE NOT NULL,
  listing_id UUID REFERENCES public.listings(id) ON DELETE SET NULL,
  user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL,
  rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
  comment TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Create profiles table for additional user info
CREATE TABLE public.profiles (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  user_id UUID REFERENCES auth.users(id) ON DELETE CASCADE NOT NULL UNIQUE,
  full_name TEXT,
  avatar_url TEXT,
  bio TEXT,
  phone TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Enable RLS on all tables
ALTER TABLE public.organizations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.listings ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.applications ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.reviews ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;

-- Organizations policies (public read, admin write)
CREATE POLICY "Organizations are viewable by everyone" 
ON public.organizations FOR SELECT 
USING (true);

-- Listings policies (public read for active listings)
CREATE POLICY "Active listings are viewable by everyone" 
ON public.listings FOR SELECT 
USING (is_active = true);

-- Applications policies
CREATE POLICY "Users can view their own applications" 
ON public.applications FOR SELECT 
USING (auth.uid() = user_id);

CREATE POLICY "Authenticated users can create applications" 
ON public.applications FOR INSERT 
TO authenticated
WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update their own applications" 
ON public.applications FOR UPDATE 
USING (auth.uid() = user_id);

-- Reviews policies
CREATE POLICY "Reviews are viewable by everyone" 
ON public.reviews FOR SELECT 
USING (true);

CREATE POLICY "Authenticated users can create reviews" 
ON public.reviews FOR INSERT 
TO authenticated
WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update their own reviews" 
ON public.reviews FOR UPDATE 
USING (auth.uid() = user_id);

CREATE POLICY "Users can delete their own reviews" 
ON public.reviews FOR DELETE 
USING (auth.uid() = user_id);

-- Profiles policies
CREATE POLICY "Profiles are viewable by everyone" 
ON public.profiles FOR SELECT 
USING (true);

CREATE POLICY "Users can create their own profile" 
ON public.profiles FOR INSERT 
TO authenticated
WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Users can update their own profile" 
ON public.profiles FOR UPDATE 
USING (auth.uid() = user_id);

-- Create function to update timestamps
CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SET search_path = public;

-- Create triggers for automatic timestamp updates
CREATE TRIGGER update_organizations_updated_at
BEFORE UPDATE ON public.organizations
FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_listings_updated_at
BEFORE UPDATE ON public.listings
FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_applications_updated_at
BEFORE UPDATE ON public.applications
FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER update_profiles_updated_at
BEFORE UPDATE ON public.profiles
FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- Create trigger to auto-create profile on user signup
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS TRIGGER AS $$
BEGIN
  INSERT INTO public.profiles (user_id, full_name)
  VALUES (NEW.id, NEW.raw_user_meta_data->>'full_name');
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER SET search_path = public;

CREATE TRIGGER on_auth_user_created
AFTER INSERT ON auth.users
FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- Insert sample organizations
INSERT INTO public.organizations (name, description, email) VALUES
('Dzīvnieku draugi', 'Organizācija, kas rūpējas par bezpajumtnieku dzīvniekiem', 'info@dzivniekudrugi.lv'),
('Latvijas Skautu un Gaidu organizācija', 'Jaunatnes organizācija, kas veicina bērnu un jauniešu attīstību', 'info@skautiguidi.lv'),
('Zaļā Latvija', 'Vides aizsardzības organizācija', 'info@zalalatvija.lv'),
('Sarkanais Krusts', 'Humānā palīdzības organizācija', 'info@redcross.lv'),
('Tech4Good', 'IT risinājumi labdarībai', 'info@tech4good.lv'),
('Latvijas Kultūras fonds', 'Kultūras pasākumu organizēšana', 'info@kulturasfonds.lv'),
('Gribu palīdzēt bēgļiem', 'Atbalsts bēgļiem Latvijā', 'info@beglupalidz.lv'),
('Bērnu mākslas centrs', 'Radošās nodarbības bērniem', 'info@bernumakslascentrs.lv');

-- Insert sample listings
INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'Palīdzība dzīvnieku patversmē',
  'Meklējam brīvprātīgos, kas palīdzētu rūpēties par kaķiem un suņiem patversmē. Darbs ietver barošanu, pastaigas un spēlēšanos.',
  id, 'Rīga', 'Dzīvnieki', '4-8h nedēļā', 5, true, false, false
FROM public.organizations WHERE name = 'Dzīvnieku draugi';

INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'Bērnu nometnes vadītājs',
  'Vasaras nometnes organizēšana un vadīšana bērniem vecumā no 8-14 gadiem. Nepieciešama pieredze darbā ar bērniem.',
  id, 'Jūrmala', 'Izglītība', 'Pilna laika (2 nedēļas)', 10, false, true, false
FROM public.organizations WHERE name = 'Latvijas Skautu un Gaidu organizācija';

INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'Vides sakopšanas talka',
  'Piedalies pludmales sakopšanas talkā! Nodrošinām inventāru un ēdināšanu. Aicinām visus dabas draugus.',
  id, 'Liepāja', 'Vide', '1 diena', 50, true, false, false
FROM public.organizations WHERE name = 'Zaļā Latvija';

INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'Senioru apmeklēšana veco ļaužu namā',
  'Pavadi laiku ar senioriem, izlasi grāmatu, parunājies vai vienkārši uzklausi. Tava klātbūtne var kādam padarīt dienu gaišāku.',
  id, 'Daugavpils', 'Sociālais darbs', '2-4h nedēļā', 8, false, false, false
FROM public.organizations WHERE name = 'Sarkanais Krusts';

INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'IT konsultants nevalstiskām organizācijām',
  'Palīdzi NVO ar mājas lapu izveidi, sociālo tīklu pārvaldību un digitālo risinājumu ieviešanu.',
  id, 'Tiešsaiste', 'IT', 'Elastīgs grafiks', 15, false, false, true
FROM public.organizations WHERE name = 'Tech4Good';

INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'Pasākumu organizatora palīgs',
  'Palīdzība kultūras pasākumu organizēšanā - apmeklētāju sagaidīšana, informācijas sniegšana, vispārēja koordinācija.',
  id, 'Rīga', 'Kultūra', 'Pasākumu laikā', 20, false, false, false
FROM public.organizations WHERE name = 'Latvijas Kultūras fonds';

INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'Tulkotājs ukraiņu bēgļiem',
  'Nepieciešami brīvprātīgie ar ukraiņu vai krievu valodas zināšanām, lai palīdzētu ar tulkošanu.',
  id, 'Rīga', 'Sociālais darbs', 'Pēc nepieciešamības', 25, false, true, false
FROM public.organizations WHERE name = 'Gribu palīdzēt bēgļiem';

INSERT INTO public.listings (title, description, organization_id, location, category, time_commitment, spots, is_new, is_urgent, is_online) 
SELECT 
  'Mākslas darbnīcu vadīšana bērniem',
  'Radošās darbnīcas vadīšana bērniem vecumā no 5-12 gadiem. Nepieciešama pieredze mākslā un darbā ar bērniem.',
  id, 'Ventspils', 'Kultūra', '4h nedēļā', 3, true, false, false
FROM public.organizations WHERE name = 'Bērnu mākslas centrs';