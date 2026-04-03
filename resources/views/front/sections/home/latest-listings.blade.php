@php
	$sectionOptions = $latestListingsOptions ?? [];
	
	$itemsInCarousel = $sectionOptions['items_in_carousel'] ?? '0';
	$isCarouselEnabled = ($itemsInCarousel == '1');
	$widgetType = $isCarouselEnabled ? 'carousel' : 'normal';
	
	$cssClasses = $sectionOptions['css_classes'] ?? '';
	$cssClasses = !empty($cssClasses) ? " {$cssClasses}" : '';
	
	$sectionData ??= [];
	$widget = (array)($sectionData['latest'] ?? []);
	
	$widgetView = 'front.search.partials.posts.widget.' . $widgetType;
@endphp
@if (view()->exists($widgetView))
	@include($widgetView, [
		'widget'         => $widget,
		'sectionOptions' => $sectionOptions
	])
@endif
