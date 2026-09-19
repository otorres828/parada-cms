 <a href="{{ $route }}" wire:navigate
     class="flex flex-col items-center gap-0.5 {{ $liveActive ? 'text-primary font-bold' : 'text-on-surface-variant' }} transition-colors">
     <span class="material-symbols-outlined text-[22px]"
         style="{{ $liveActive ? "font-variation-settings: 'FILL' 1;" : '' }}">
         {{ $icon }}
     </span>
     <span class="text-[10px] font-label-sm">{{ $title }}</span>
 </a>
