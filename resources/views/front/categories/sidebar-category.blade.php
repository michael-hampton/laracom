<ul class="nav sidebar-menu categories-list">
    @foreach($categories as $category)
    @if($category->children()->count() > 0)
    <li>@include('layouts.front.category-sidebar-sub', ['subs' => $category->children])</li>
    @else
    <li category-id="{{$category->id}}" @if(request()->segment(2) == $category->slug) class="active" @endif><a href="{{ route('front.category.slug', $category->slug) }}">{{ $category->name }}</a></li>
    @endif
    @endforeach
</ul>