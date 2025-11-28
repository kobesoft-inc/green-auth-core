<div class="flex w-full items-center gap-x-3 px-3 py-4">
    @if($isBlank)
        {!! $placeholder !!}
    @else
        @if($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $name }}"
                @class([
                    'object-cover object-center flex-shrink-0',
                    'rounded-full' => $isCircular,
                    'rounded-lg' => !$isCircular,
                ])
                style="width: {{ $size }}; height: {{ $size }};"
            />
        @else
            <div
                @class([
                    'bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0',
                    'rounded-full' => $isCircular,
                    'rounded-lg' => !$isCircular,
                ])
                style="width: {{ $size }}; height: {{ $size }};"
            >
                @if($name)
                    <span class="text-gray-500 dark:text-gray-400 font-medium" style="font-size: calc({{ $size }} * 0.4);">
                        {{ mb_substr($name, 0, 1) }}
                    </span>
                @else
                    <x-filament::icon
                        icon="heroicon-o-user"
                        class="text-gray-400 dark:text-gray-500"
                        style="width: calc({{ $size }} * 0.6); height: calc({{ $size }} * 0.6);"
                    />
                @endif
            </div>
        @endif

        @if($name)
            <span class="text-sm text-gray-950 dark:text-white font-medium truncate">
                {{ $name }}
            </span>
        @endif
    @endif
</div>
