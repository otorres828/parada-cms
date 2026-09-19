<style>
    .form-check-input {
        -ms-transform: scale(2);
        -moz-transform: scale(2);
        -webkit-transform: scale(1);
        -o-transform: scale(2);
    }
    #accordionGroups,
    #accordionGroups .accordion-item,
    #accordionGroups .accordion-header,
    #accordionGroups .accordion-body,
    #accordionGroups .accordion-button,
    #accordionGroups label {
        color: #000 !important;
    }
    #accordionGroups .accordion-button {
        text-decoration: none;
        font-weight: 600;
    }
</style>


@if (isset($groups) && $groups->count())

<div x-data='{ openGroups: @json([$groups->first()->id]) }' class="accordion" id="accordionGroups">
        @foreach ($groups as $groupIndex => $group)
            <div class="accordion-item mb-2 border rounded">
                <h2 class="accordion-header" id="headingGroup{{ $group->id }}">
                    <button type="button"
                        class="accordion-button d-flex justify-content-between align-items-center w-100"
                        :class="openGroups.includes({{ $group->id }}) ? '' : 'collapsed'"
                        @click="openGroups.includes({{ $group->id }}) ? openGroups = openGroups.filter(i => i !== {{ $group->id }}) : openGroups = [...openGroups, {{ $group->id }}]"
                        :aria-expanded="openGroups.includes({{ $group->id }})"
                        style="background:transparent;border:none;padding:.75rem 1rem;">
                        <span class="text-start">{{ $group->name }}</span>
                    </button>
                </h2>

                <div x-show="openGroups.includes({{ $group->id }})" x-transition class="accordion-body px-0 py-2">
                    <div class="p-2">

                        @foreach ($group->sections as $section)
                            @php
                                $check = 0;
                                foreach ($section->permissions->where('status', 1) as $permiso) {
                                    if (in_array($permiso->id, $selectedPermissions)) {
                                        $check++;
                                    }
                                }
                            @endphp

                            <x-form.permit-card wire:ignore>

                                <x-slot:title>
                                    {{ $section->name }}
                                </x-slot:title>

                                <x-slot:content>

                                <div class="form-check mb-3">
                                    <input class="form-check-input ch_t" type="checkbox"
                                                onclick="todo({{ $section->id }}, this)"
                                                data-section-id="{{ $section->id }}" id="todo_{{ $section->id }}"
                                                {{ $check == count($section->permissions) ? 'checked' : '' }} />

                                    <label class="form-check-label text-secondary" for="todo_{{ $section->id }}">
                                        Todos los permisos:
                                    </label>
                                </div>

                                    @foreach ($section->permissions->where('status', 1) as $permiso)
                                         <div class="form-check form-check-inline mb-3">

                                                <input class="form-check-input ch_t" type="checkbox"
                                                    onclick="seleccionar({{ $permiso->id }}, this, {{ $section->id }})"
                                                    id="check_{{ $permiso->id }}"
                                                    data-section-id="{{ $section->id }}"
                                                    {{ in_array($permiso->id, $selectedPermissions) ? 'checked' : '' }}
                                                    value="{{ $permiso->url }}" />

                                              <label class="form-check-label" for="check_{{ $permiso->id }}">
                                                {{ $permiso->name }}
                                            </label>
                                        </div>
                                    @endforeach

                                </x-slot:content>
                            </x-form.permit-card>
                        @endforeach

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p>No hay permisos para mostrar.</p>
@endif
