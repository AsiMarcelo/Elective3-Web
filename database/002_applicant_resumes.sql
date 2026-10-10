-- Resume files live in a private Supabase Storage bucket. The applicants table
-- stores only the object path and file metadata.
ALTER TABLE public.applicants
    ADD COLUMN IF NOT EXISTS resume_storage_path text,
    ADD COLUMN IF NOT EXISTS resume_original_name text,
    ADD COLUMN IF NOT EXISTS resume_mime_type text,
    ADD COLUMN IF NOT EXISTS resume_size_bytes bigint,
    ADD COLUMN IF NOT EXISTS resume_uploaded_at timestamptz;

-- Create/update a private bucket limited to PDF and Word resumes up to 5 MiB.
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
    'applicant-resumes',
    'applicant-resumes',
    false,
    5242880,
    ARRAY['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
)
ON CONFLICT (id) DO UPDATE
SET public = false,
    file_size_limit = EXCLUDED.file_size_limit,
    allowed_mime_types = EXCLUDED.allowed_mime_types;
