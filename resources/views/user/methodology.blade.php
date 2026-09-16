@extends('layouts.user')

@section('title', ($language === 'en' ? 'Methodology' : 'Metodologi') . ' - EMBER')

@section('content')

    @php
        $content = $methodology?->{'content_' . $language};
    @endphp

    <section class="w-full max-w-none p-0 m-0">
        @if ($methodology)

            <div class="rich-content w-full max-w-none m-0 p-0 text-lg leading-8 text-slate-700">
                {!! $content ?: e(
                    $language === 'en'
                        ? 'Methodology content is not available yet.'
                        : 'Konten Methodology belum tersedia.'
                ) !!}
            </div>

        @else

            <p class="m-0 border border-slate-200 bg-white p-8 text-slate-500">
                {{ $language === 'en'
                    ? 'Methodology content is not available yet.'
                    : 'Konten Methodology belum tersedia.' }}
            </p>

        @endif
    </section>

@endsection