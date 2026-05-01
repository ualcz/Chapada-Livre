@php
	use App\Helpers\Common\Date\TimeZoneManager;
	use Illuminate\Support\Carbon;
	
	$authUser = auth()->check() ? auth()->user() : null;
	$authUserId = !empty($authUser) ? $authUser->getAuthIdentifier() : 0;
	
	$thread ??= [];
	$message ??= [];
	
	$filePath = data_get($message, 'file_path');
    
    // Modern date formatting
    $createdAt = new Carbon(data_get($message, 'created_at'));
    $createdAt->timezone(TimeZoneManager::getContextualTimeZone());
    if ($createdAt->isToday()) {
        $displayTime = $createdAt->format('H:i');
    } else {
        $displayTime = $createdAt->format('d/m/y H:i');
    }
@endphp
@if ($authUserId == data_get($message, 'user.id'))
    <div class="messenger-message me">
        <div class="message-bubble">
            <div class="message-text">
                {!! urlsToLinks(nlToBr(data_get($message, 'body')), ['class' => 'text-white']) !!}
            </div>
            @if (!empty($filePath) && $disk->exists($filePath))
                <div class="mt-2 small border-top pt-1 border-white border-opacity-25">
                    <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                    <a class="text-white text-decoration-underline" href="{{ privateFileUrl($filePath, null) }}" target="_blank">
                        {{ str($filePath)->basename()->limit(20) }}
                    </a>
                </div>
            @endif
            <div class="message-meta">
                <span class="message-time">{{ $displayTime }}</span>
                @php
                    $recipient = data_get($message, 'p_recipient');
                    $tz = TimeZoneManager::getContextualTimeZone();
                    $threadUpdatedAt = new Carbon(data_get($thread, 'updated_at'));
                    $threadUpdatedAt->timezone($tz);
                    $recipientLastRead = new Carbon(data_get($recipient, 'last_read'));
                    $recipientLastRead->timezone($tz);
                    $threadIsUnreadByThisRecipient = (!empty($recipient) && (data_get($recipient, 'last_read') === null || $threadUpdatedAt->gt($recipientLastRead)));
                @endphp
                @if ($threadIsUnreadByThisRecipient)
                    <i class="fa-solid fa-check"></i>
                @else
                    <i class="fa-solid fa-check-double text-info"></i>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="messenger-message user d-flex gap-2">
        <img src="{{ url(data_get($message, 'user.photo_url')) }}" class="rounded-circle align-self-end mb-4" style="width: 30px; height: 30px;">
        <div>
            <div class="message-bubble">
                <div class="message-text">
                    {!! urlsToLinks(nlToBr(data_get($message, 'body')), ['class' => linkClass()]) !!}
                </div>
                @if (!empty($filePath) && $disk->exists($filePath))
                    <div class="mt-2 small border-top pt-1">
                        <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                        <a class="{{ linkClass() }}" href="{{ privateFileUrl($filePath, null) }}" target="_blank">
                            {{ str($filePath)->basename()->limit(20) }}
                        </a>
                    </div>
                @endif
                <div class="message-meta">
                    <span class="message-time">{{ $displayTime }}</span>
                </div>
            </div>
        </div>
    </div>
@endif

