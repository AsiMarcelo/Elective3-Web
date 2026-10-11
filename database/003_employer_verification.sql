-- Add verification review fields to employer profiles.
ALTER TABLE public.employers
    ADD COLUMN IF NOT EXISTS verification_status text,
    ADD COLUMN IF NOT EXISTS verification_submitted_at timestamptz,
    ADD COLUMN IF NOT EXISTS verification_reviewed_at timestamptz,
    ADD COLUMN IF NOT EXISTS verification_reviewed_by bigint,
    ADD COLUMN IF NOT EXISTS verification_notes text;

UPDATE public.employers
SET verification_status = 'pending'
WHERE verification_status IS NULL;

UPDATE public.employers
SET verification_submitted_at = CURRENT_TIMESTAMP
WHERE verification_submitted_at IS NULL;

ALTER TABLE public.employers
    ALTER COLUMN verification_status SET DEFAULT 'pending',
    ALTER COLUMN verification_status SET NOT NULL,
    ALTER COLUMN verification_submitted_at SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN verification_submitted_at SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'employers_verification_status_check'
          AND conrelid = 'public.employers'::regclass
    ) THEN
        ALTER TABLE public.employers
            ADD CONSTRAINT employers_verification_status_check
            CHECK (verification_status IN ('pending', 'verified', 'rejected'));
    END IF;
END $$;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'employers_verification_reviewed_by_fkey'
          AND conrelid = 'public.employers'::regclass
    ) THEN
        ALTER TABLE public.employers
            ADD CONSTRAINT employers_verification_reviewed_by_fkey
            FOREIGN KEY (verification_reviewed_by) REFERENCES public.users(id) ON DELETE SET NULL;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS employers_verification_status_idx
    ON public.employers (verification_status);
