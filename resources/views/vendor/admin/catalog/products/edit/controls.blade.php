@if ($attribute->code === 'price_display_mode')
    @php
        $currentMode = old('price_display_mode', $product['price_display_mode'] ?? '');

        if (! in_array($currentMode, array_keys(\App\Support\WholesalePricingManager::DISPLAY_MODES), true)) {
            $currentMode = '';
        }
    @endphp

    <v-field
        name="price_display_mode"
        value="{{ $currentMode }}"
        label="{{ $attribute->admin_name }}"
        v-slot="{ field }"
    >
        <select
            id="price_display_mode"
            name="price_display_mode"
            v-bind="field"
            class="custom-select w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
        >
            <option value="" @selected($currentMode === '')>
                Native Bagisto Pricing (standard price &amp; checkout)
            </option>

            @foreach (\App\Support\WholesalePricingManager::DISPLAY_MODES as $modeValue => $modeLabel)
                <option
                    value="{{ $modeValue }}"
                    @selected($currentMode === $modeValue)
                >
                    {{ $modeLabel }}
                </option>
            @endforeach
        </select>
    </v-field>

    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        "Native Bagisto Pricing" uses the standard price and checkout. "Price Range" and "Contact for Price" show indicative wholesale information only; orders then require an approved quotation. The native price above is never changed by wholesale values.
    </p>

@elseif (in_array($attribute->code, ['wholesale_min_price', 'wholesale_max_price'], true))
    @php
        $rawPrice = old($attribute->code, $product[$attribute->code]);
        $formattedPrice = ($rawPrice !== null && $rawPrice !== '' && is_numeric($rawPrice))
            ? number_format((float) $rawPrice, 2, '.', '')
            : '';
    @endphp

    <x-admin::form.control-group.control
        type="price"
        :id="$attribute->code"
        :name="$attribute->code"
        ::rules="{ decimal: true, min_value: 0 }"
        :value="$formattedPrice"
        :label="$attribute->admin_name"
    >
        <x-slot:currency class="dark:text-gray-300">
            {{ core()->currencySymbol(core()->getBaseCurrencyCode()) }}
        </x-slot>
    </x-admin::form.control-group.control>

    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        @if ($attribute->code === 'wholesale_min_price')
            Optional when "Contact for Price" is selected. Must not be negative.
        @else
            Optional when "Contact for Price" is selected. Must be &ge; Minimum Wholesale Price.
        @endif
    </p>
@elseif ($attribute->code === 'wholesale_moq')
    @php
        $rawMoq = old('wholesale_moq', $product['wholesale_moq']);
    @endphp

    <v-field
        type="text"
        name="wholesale_moq"
        :rules="{ decimal: true }"
        value="{{ $rawMoq }}"
        v-slot="{ field }"
        label="{{ $attribute->admin_name }}"
    >
        <input
            type="text"
            id="wholesale_moq"
            placeholder="e.g. 50 or 0.5"
            :class="[errors['wholesale_moq'] ? 'border border-red-600 hover:border-red-600' : '']"
            class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
            name="wholesale_moq"
            v-bind="field"
        >
    </v-field>

    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Minimum Order Quantity (&gt; 0 when configured). Supports whole or decimal quantities.
    </p>
@elseif ($attribute->code === 'wholesale_unit')
    @php
        $currentUnit = old('wholesale_unit', $product['wholesale_unit'] ?? '');
    @endphp

    <v-field
        name="wholesale_unit"
        value="{{ $currentUnit }}"
        label="{{ $attribute->admin_name }}"
        v-slot="{ field }"
    >
        <select
            id="wholesale_unit"
            name="wholesale_unit"
            v-bind="field"
            class="custom-select w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
        >
            <option value="">— Select Unit —</option>

            @foreach (\App\Support\WholesalePricingManager::UNITS as $unitValue => $unitLabel)
                <option
                    value="{{ $unitValue }}"
                    @selected($currentUnit === $unitValue)
                >
                    {{ $unitLabel }}
                </option>
            @endforeach
        </select>
    </v-field>
@elseif ($attribute->code === 'supply_availability')
    @php
        $currentAvailability = old('supply_availability', $product['supply_availability'] ?? '');
    @endphp

    <v-field
        name="supply_availability"
        value="{{ $currentAvailability }}"
        label="{{ $attribute->admin_name }}"
        v-slot="{ field }"
    >
        <select
            id="supply_availability"
            name="supply_availability"
            v-bind="field"
            class="custom-select w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
        >
            <option value="">— Select Supply Availability —</option>

            @foreach (\App\Support\WholesalePricingManager::AVAILABILITY_OPTIONS as $availValue => $availLabel)
                <option
                    value="{{ $availValue }}"
                    @selected($currentAvailability === $availValue)
                >
                    {{ $availLabel }}
                </option>
            @endforeach
        </select>
    </v-field>
@else
    @switch($attribute->type)
        @case('text')
            <v-field
                type="text"
                name="{{ $attribute->code }}"
                :rules="{{ $attribute->validations }}"
                value="{{ old($attribute->code) ?: $product[$attribute->code] }}"
                v-slot="{ field }"
                label="{{ $attribute->admin_name }}"
            >
                <input
                    type="text"
                    id="{{ $attribute->code }}"
                    :class="[errors['{{ $attribute->code }}'] ? 'border border-red-600 hover:border-red-600' : '']"
                    class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                    name="{{ $attribute->code }}"
                    v-bind="field"
                    @if ($attribute->code == 'url_key') v-slugify @endif
                    @if ($attribute->code == 'name') v-slugify-target:url_key="setValues" @endif
                >
            </v-field>

            @break
        @case('price')
            @php
                $priceControlValue = old($attribute->code);

                if ($priceControlValue === null || $priceControlValue === '') {
                    $priceControlValue = $product[$attribute->code];
                }

                if (($priceControlValue === null || $priceControlValue === '') && $attribute->code === 'price') {
                    $priceControlValue = '0.00';
                }
            @endphp

            <x-admin::form.control-group.control
                type="price"
                :id="$attribute->code"
                :class="($attribute->code == 'price' ? 'py-2.5 bg-gray-50 text-xl font-bold' : '')"
                :name="$attribute->code"
                ::rules="{{ $attribute->validations }}"
                :value="$priceControlValue"
                :label="$attribute->admin_name"
            >
                <x-slot:currency :class="'dark:text-gray-300 ' . ($attribute->code == 'price' ? 'bg-gray-50 dark:bg-gray-900 text-xl' : '')">
                    {{ core()->currencySymbol(core()->getBaseCurrencyCode()) }}
                </x-slot>
            </x-admin::form.control-group.control>

            @break
        @case('textarea')
            <x-admin::form.control-group.control
                type="textarea"
                :id="$attribute->code"
                :name="$attribute->code"
                ::rules="{{ $attribute->validations }}"
                value="{{ old($attribute->code) ?: $product[$attribute->code] }}"
                :label="$attribute->admin_name"
                :tinymce="(bool) $attribute->enable_wysiwyg"
            />

            @break
        @case('date')
            <x-admin::form.control-group.control
                type="date"
                :id="$attribute->code"
                :name="$attribute->code"
                ::rules="{{ $attribute->validations }}"
                value="{{ old($attribute->code) ?: $product[$attribute->code] }}"
                :label="$attribute->admin_name"
            />

            @break
        @case('datetime')
            <x-admin::form.control-group.control
                type="datetime"
                :name="$attribute->code"
                ::rules="{{ $attribute->validations }}"
                value="{{ old($attribute->code) ?: $product[$attribute->code] }}"
                :label="$attribute->admin_name"
            />

            @break
        @case('select')
            @php
                $selectedOption = old($attribute->code) ?: $product[$attribute->code];

                if ($attribute->code === 'tax_category_id') {
                    $options = app('Webkul\Tax\Repositories\TaxCategoryRepository')->all();
                } else if ($attribute->code === 'rma_rule_id') {
                    $rmaRuleRepository = app('Webkul\RMA\Repositories\RMARuleRepository');

                    $options = $rmaRuleRepository->getActiveRules();

                    if (
                        $selectedOption
                        && ! $options->contains('id', $selectedOption)
                    ) {
                        $currentRule = $rmaRuleRepository->find($selectedOption);

                        if ($currentRule) {
                            $options->push($currentRule);
                        }
                    }
                } else {
                    $options = $attribute->options()->orderBy('sort_order')->get();
                }

                $selectOptions = collect($options)
                    ->map(fn ($option) => ['id' => (string) $option->id, 'label' => $option->admin_name ?? $option->name])
                    ->values();
            @endphp

            <x-admin::form.control-group.advance.select
                :name="$attribute->code"
                :options="$selectOptions"
                :value="(string) $selectedOption"
                :placeholder="$attribute->admin_name"
                :label="$attribute->admin_name"
                ::clearable="{{ $attribute->is_required ? 'false' : 'true' }}"
                ::rules="{{ $attribute->validations }}"
            />

            @break
        @case('multiselect')
            @php
                $selectedOption = old($attribute->code) ?: array_filter(explode(',', $product[$attribute->code] ?? ''), fn ($id) => $id !== '');

                $multiselectOptions = $attribute->options()->orderBy('sort_order')->get()
                    ->map(fn ($option) => ['id' => (string) $option->id, 'label' => $option->admin_name])
                    ->values();
            @endphp

            <x-admin::form.control-group.advance.multiselect
                :name="$attribute->code . '[]'"
                :options="$multiselectOptions"
                :value="array_values((array) $selectedOption)"
                :placeholder="$attribute->admin_name"
                :label="$attribute->admin_name"
                ::rules="{{ $attribute->validations }}"
            />

            @break
        @case('checkbox')
            @php
                $selectedOption = old($attribute->code) ?: explode(',', $product[$attribute->code]);
            @endphp

            @foreach ($attribute->options as $option)
                <div class="mb-2 flex items-center gap-2.5 last:!mb-0">
                    <x-admin::form.control-group.control
                        type="checkbox"
                        :id="$attribute->code . '_' . $option->id"
                        :name="$attribute->code . '[]'"
                        ::rules="{{ $attribute->validations }}"
                        :value="$option->id"
                        :for="$attribute->code . '_' . $option->id"
                        :label="$attribute->admin_name"
                        :checked="in_array($option->id, $selectedOption)"
                    />

                    <label
                        class="cursor-pointer select-none text-xs font-medium text-gray-600 dark:text-gray-300"
                        for="{{ $attribute->code . '_' . $option->id }}"
                        v-pre
                    >
                        {{ $option->admin_name }}
                    </label>
                </div>
            @endforeach

            @break
        @case('boolean')
            @php $selectedValue = old($attribute->code) ?: $product[$attribute->code] @endphp

            <x-admin::form.control-group.control
                type="switch"
                :id="$attribute->code"
                :name="$attribute->code"
                :value="1"
                :label="$attribute->admin_name"
                :checked="(boolean) $selectedValue"
            />

            @break
        @case('image')
        @case('file')
            @php
                $fileRules = $product[$attribute->code]
                    ? preg_replace('/required:\s*true\s*,?\s*/', '', $attribute->validations)
                    : $attribute->validations;
            @endphp

            <div class="flex gap-2.5">
                @if ($product[$attribute->code])
                    <a
                        href="{{ route('admin.catalog.products.file.download', [$product->id, $attribute->id] )}}"
                        class="flex"
                    >
                        @if ($attribute->type == 'image')
                            @if (Storage::exists($product[$attribute->code]))
                                <img
                                    src="{{ Storage::url($product[$attribute->code]) }}"
                                    class="h-[45px] w-[45px] overflow-hidden rounded border hover:border-gray-400 dark:border-gray-800"
                                />
                            @endif
                        @else
                            <div class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-1 rounded-md border border-transparent p-1.5 text-center text-gray-600 transition-all marker:shadow hover:bg-gray-200 active:border-gray-300 dark:text-gray-300 dark:hover:bg-gray-800">
                                <i class="icon-down-stat text-2xl"></i>
                            </div>
                        @endif
                    </a>

                    <input
                        type="hidden"
                        name="{{ $attribute->code }}"
                        value="{{ $product[$attribute->code] }}"
                    />
                @endif

                <v-field
                    type="file"
                    class="w-full"
                    name="{{ $attribute->code }}"
                    :rules="{{ $fileRules }}"
                    v-slot="{ handleChange, handleBlur }"
                    label="{{ $attribute->admin_name }}"
                >
                    <input
                        type="file"
                        id="{{ $attribute->code }}"
                        :class="[errors['{{ $attribute->code }}'] ? 'border border-red-600 hover:border-red-600' : '']"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:text-gray-300 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="{{ $attribute->code }}"
                        @change="handleChange"
                        @blur="handleBlur"
                    >
                </v-field>
            </div>

            @if ($product[$attribute->code] && ! $attribute->is_required)
                <div class="mt-2.5 flex items-center gap-2.5">
                    <x-admin::form.control-group.control
                        type="checkbox"
                        :id="$attribute->code . '_delete'"
                        :name="$attribute->code . '[delete]'"
                        value="1"
                        :for="$attribute->code . '_delete'"
                    />

                    <label
                        for="{{ $attribute->code . '_delete' }}"
                        class="cursor-pointer select-none text-sm text-gray-600 dark:text-gray-300"
                    >
                        @lang('admin::app.catalog.products.edit.remove')
                    </label>
                </div>
            @endif

            @break
    @endswitch
@endif

