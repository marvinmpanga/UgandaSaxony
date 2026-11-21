import { Head, Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import { PublicLayout } from '@/layouts/PublicLayout';

interface ErrorPageProps {
    status: number;
}

const MESSAGES: Record<number, { title: string; message: string }> = {
    404: {
        title: 'Page Not Found',
        message: 'What you’re looking for could not be found.',
    },
    403: {
        title: 'Access Denied',
        message: 'You don’t have permission to view this page.',
    },
    500: {
        title: 'Server Error',
        message: 'Something went wrong on our end. Please try again shortly.',
    },
    503: {
        title: 'Temporarily Unavailable',
        message: 'The site is temporarily unavailable. Please check back soon.',
    },
};

export default function ErrorPage({ status }: ErrorPageProps) {
    const info = MESSAGES[status] ?? {
        title: 'Error',
        message: 'Something went wrong. Please try again.',
    };

    return (
        <PublicLayout hasDarkHero={false}>
            <Head title={`${status} — ${info.title}`} />

            <section className="flex min-h-[70vh] items-center justify-center px-5 pt-32 pb-20 sm:px-8 sm:pt-40 sm:pb-28">
                <div className="mx-auto flex max-w-lg flex-col items-center text-center">
                    <img
                        src="/images/404.svg"
                        alt={info.title}
                        className="w-full max-w-sm"
                    />
                    <p className="mt-8 text-lg leading-relaxed text-muted-foreground sm:text-xl">
                        {info.message}
                    </p>
                    <Link
                        href="/"
                        className="mt-7 inline-flex items-center gap-2 rounded-full bg-primary px-7 py-3 text-sm font-semibold text-white transition hover:bg-brand-dark"
                    >
                        Back to homepage
                        <ArrowUpRight className="h-4 w-4" />
                    </Link>
                </div>
            </section>
        </PublicLayout>
    );
}
