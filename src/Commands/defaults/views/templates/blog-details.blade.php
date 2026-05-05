@extends('layouts.index')

@section('meta-description')
    <meta name="description" content="{{ $page->excerpt }}"/>
@stop

@section('facebook-og')
    @php
        if (isset($page->data->facebook_cover_photo)) {
            $image = image()->load($page->data->facebook_cover_photo)->width(1200)->height(630)->object();
        }
        $listingPage = pages()->find(PAGE_ID);
    @endphp
    <meta property="og:url"                content="{{ request()->url() }}" />
    <meta property="og:type"               content="article" />
    <meta property="og:title"              content="{{ $page->name }}" />
    <meta property="og:description"        content="{{ $page->excerpt }}" />
    @if (isset($image->src))
        <meta property="og:image"              content="{{ asset($image->src) }}" />
    @endif
    @if(isset($image->width))
        <meta property="og:image:width"        content="{{ $image->width }}" />
    @endif
    @if(isset($image->height))
        <meta property="og:image:height"       content="{{ $image->height }}" />
    @endif
@stop

@section('template')

    @include('templates.includes.content')

    <section class="page__block page__article-details">
        <div class="holder holder--small">
            <article class="article-details">
                <header class="article-details__header">
                    <h4 class="heading--title fade-in-up">{{ $page->published_at->format('jS M Y') }}</h4>
                    <h1 class="heading fade-in-up">{{ $page->name }}</h1>
                </header>

                <div class="article-details__content fade-in-up">
                    {!! $page->text !!}
                </div>


                <footer>
                    <a href="{{ $listingPage->meta->uri }}" class="button fade-in-up">Back to {{ $listingPage->name }}</a>
                </footer>
            </article>
        </div>

    </section>
@stop


