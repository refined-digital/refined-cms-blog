@php
    $classes = array_merge($classes, [
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ]);

    $repo = new \RefinedDigital\Blog\Module\Http\Repositories\BlogRepository();
    $data = $repo->getForFront(8);
    $newsPageLink = pages()->getPageLink(PAGE_ID).'/';
@endphp

<section class="{{ implode(' ', $classes) }}" id="page-block--{{ $page->id }}-{{ $index }}">
    <div class="holder">
        <div class="articles grid">
            @if ($data->count() > 4)
                {!! view()->make('templates.includes.blog-downloads')->with(compact('content'))->with('class', 'display-desktop') !!}
            @endif
            @if ($data->count())
                @foreach ($data as $item)
                    <article class="release__item article__item fade-in-up">
                        <a href="{{ $newsPageLink.$item->meta->uri }}">
                            <span class="release__item-hover">@include('icons.icon')</span>
                            <figure>
                                {!!
                                  image()
                                    ->load($item->image)
                                    ->fit()
                                    ->dimensions([
                                        ['media' => 800, 'width' => 600, 'height' => 550],
                                        ['width' => 600 * 0.75, 'height' => 550 * 0.75]
                                    ])
                                    ->pictureHtml()
                                !!}
                            </figure>
                            <div class="article__item-content">
                                <h5 class="date">{{ $item->published_at->format('jS F Y') }}</h5>
                                <h3>{{ $item->name }}</h3>
                                <div class="cont">
                                    {!!  $item->excerpt !!}
                                </div>
                                <footer>
                                    <a href="{{ $newsPageLink.$item->meta->uri }}" class="button button--blue">
                                        Read more
                                    </a>
                                </footer>
                            </div>
                        </a>
                    </article>
                @endforeach
            @endif

            @if ($data->count() < 5)
                {!! view()->make('templates.includes.blog-downloads')->with(compact('content')) !!}
            @else
                {!! view()->make('templates.includes.blog-downloads')->with(compact('content'))->with('class', 'display-mobile') !!}
            @endif
        </div>

        @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->total() > $data->perPage())
            {!! $data->appends(request()->except(['page', 'done']))->links('templates.includes.pagination') !!}
        @endif
    </div>
</section>
