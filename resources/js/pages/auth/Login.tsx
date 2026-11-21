import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Eye, EyeOff } from 'lucide-react';
import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const SHOWCASE_IMAGE = '/images/legacy/hero-2.jpg';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = React.useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <div className="theme-public relative flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#20534f] p-4 sm:p-8 lg:p-10">
            <Head title="Staff Sign In" />

            {/* SVG clip-path for the subtle slanted right edge on desktop */}
            <svg width="0" height="0" className="absolute" aria-hidden="true">
                <defs>
                    <clipPath id="login-showcase-slant" clipPathUnits="objectBoundingBox">
                        <path d="M 0.055,0 L 0.942,0 Q 1,0 0.996,0.045 L 0.932,0.955 Q 0.928,1 0.872,1 L 0.055,1 Q 0,1 0,0.955 L 0,0.045 Q 0,0 0.055,0 Z" />
                    </clipPath>
                </defs>
            </svg>

            {/* Full-bleed split diagonal backdrop */}
            <div className="pointer-events-none fixed inset-0 z-0" aria-hidden="true">
                <img
                    src={SHOWCASE_IMAGE}
                    alt=""
                    className="absolute inset-0 h-full w-full scale-105 object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-br from-[#20534f]/90 via-[#20534f]/80 to-[#183f3c]/92" />

                <div
                    className="hidden h-full w-full bg-[#f1f4f3] lg:block"
                    style={{ clipPath: 'polygon(64% 0%, 100% 0%, 100% 100%, 58% 100%)' }}
                />
            </div>

            {/* Main elevated split card */}
            <div className="relative z-10 grid w-full max-w-[1000px] grid-cols-1 overflow-hidden rounded-[28px] bg-white p-2.5 shadow-[0_32px_80px_-20px_rgba(9,24,23,0.45)] sm:p-3.5 lg:min-h-[600px] lg:grid-cols-[1.04fr_1fr]">
                {/* Left visual panel */}
                <div className="relative min-h-[220px] overflow-hidden rounded-[22px] bg-[#0f2624] lg:min-h-full lg:rounded-none lg:[clip-path:url(#login-showcase-slant)]">
                    <img
                        src={SHOWCASE_IMAGE}
                        alt="Uganda–Saxony Partnership"
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                    <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-black/15" />
                </div>

                {/* Right login form panel */}
                <div className="flex flex-col justify-between px-6 py-7 sm:px-10 sm:py-9 lg:px-12 lg:py-9">
                    {/* Centered form */}
                    <div className="mx-auto my-auto w-full max-w-[340px] py-4">
                        <div className="mb-8 flex flex-col items-center text-center">
                            <Link href="/" aria-label="Uganda–Saxony Partnership home" className="mb-5">
                                <img
                                    src="/images/legacy/logo.png"
                                    alt="Uganda–Saxony Partnership"
                                    className="h-20 w-20 rounded-full object-contain sm:h-24 sm:w-24"
                                />
                            </Link>
                            <h1 className="text-3xl font-extrabold tracking-tight text-[#0f2624] sm:text-[34px]">
                                Staff Sign In
                            </h1>
                            <p className="mt-2 text-sm text-[#4c6663]">
                                Authorised partnership staff only
                            </p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="email" className="sr-only">
                                    Email Address
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="Email address"
                                    autoComplete="email"
                                    required
                                    autoFocus
                                    className="h-12 rounded-xl border-[#d8e3e1] bg-white px-4 text-sm text-[#0f2624] shadow-none placeholder:text-[#7a918e] focus-visible:border-[#20534f] focus-visible:ring-[#20534f]/20"
                                />
                                {errors.email && (
                                    <p className="mt-1.5 text-xs font-medium text-destructive">{errors.email}</p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="password" className="sr-only">
                                    Password
                                </Label>
                                <div className="relative">
                                    <Input
                                        id="password"
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Password"
                                        autoComplete="current-password"
                                        required
                                        className="h-12 rounded-xl border-[#d8e3e1] bg-white pl-4 pr-11 text-sm text-[#0f2624] shadow-none placeholder:text-[#7a918e] focus-visible:border-[#20534f] focus-visible:ring-[#20534f]/20"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword((prev) => !prev)}
                                        aria-label={showPassword ? 'Hide password' : 'Show password'}
                                        className="absolute right-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-[#7a918e] transition hover:text-[#20534f]"
                                    >
                                        {showPassword ? (
                                            <EyeOff className="h-4 w-4" strokeWidth={1.75} />
                                        ) : (
                                            <Eye className="h-4 w-4" strokeWidth={1.75} />
                                        )}
                                    </button>
                                </div>
                                {errors.password && (
                                    <p className="mt-1.5 text-xs font-medium text-destructive">{errors.password}</p>
                                )}
                            </div>

                            <label className="flex cursor-pointer items-center gap-2 pt-0.5 text-xs text-[#4c6663] select-none">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="h-4 w-4 rounded border-[#c9dad7] accent-[#20534f]"
                                />
                                Keep me signed in
                            </label>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="mt-2 h-12 w-full rounded-full bg-[#20534f] text-sm font-semibold text-white shadow-lg shadow-[#20534f]/25 transition hover:bg-[#183f3c]"
                            >
                                {processing ? 'Signing in...' : 'Sign In'}
                            </Button>
                        </form>
                    </div>

                    {/* Footer link */}
                    <div className="flex justify-center pt-2">
                        <Link
                            href="/"
                            className="inline-flex items-center gap-1.5 text-xs font-medium text-[#4c6663] transition hover:text-[#20534f]"
                        >
                            <ArrowLeft className="h-3.5 w-3.5" strokeWidth={1.75} />
                            Return to Public Website
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
