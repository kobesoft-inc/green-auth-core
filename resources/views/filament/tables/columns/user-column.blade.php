<div class="flex w-full items-center gap-x-3 px-3 py-4">
    @if($isBlank)
        @if($placeholder)
            {!! $placeholder !!}
        @endif
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
                    <svg
                        class="text-gray-400 dark:text-gray-500"
                        style="width: calc({{ $size }} * 0.6); height: calc({{ $size }} * 0.6);"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                            clip-rule="evenodd"
                        />
                    </svg>
                @endif
            </div>
        @endif

        @if($name)
            <span @class([
                'text-gray-950 dark:text-white font-medium truncate flex-1 min-w-0',
                $textSize,
            ])>
                {{ $name }}
            </span>
        @endif
    @endif
</div>