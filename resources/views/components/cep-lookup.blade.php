@props([
    'required' => true,
    'type' => 'business',
])

<div x-data="cepLookup()">
    <!-- CEP Field -->
    <div class="form-group">
        <x-input-label for="postal_code" :value="'CEP' . ($required ? ' *' : '')" />
        <x-text-input
            id="postal_code"
            name="postal_code"
            type="text"
            x-model="cep"
            @input="formatCep"
            @blur="searchCep"
            :value="old('postal_code')"
            placeholder="00000-000"
            maxlength="9"
            :required="$required"
        />
        <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
        <p x-show="loading" class="mt-1 text-sm text-neutral-500">Buscando CEP...</p>
        <p x-show="error" class="mt-1 text-sm text-danger-600" x-text="error"></p>
    </div>

    <!-- Street Field -->
    <div class="form-group">
        <x-input-label for="street" :value="'Logradouro' . ($required ? ' *' : '')" />
        <x-text-input
            id="street"
            name="street"
            type="text"
            :value="old('street')"
            :required="$required"
        />
        <x-input-error class="mt-2" :messages="$errors->get('street')" />
    </div>

    <!-- Number and Complement -->
    <div class="form-grid form-grid-2">
        <div>
            <x-input-label for="number" :value="'Número' . ($required ? ' *' : '')" />
            <x-text-input
                id="number"
                name="number"
                type="text"
                :value="old('number')"
                :required="$required"
            />
            <x-input-error class="mt-2" :messages="$errors->get('number')" />
        </div>

        <div>
            <x-input-label for="complement" value="Complemento" />
            <x-text-input
                id="complement"
                name="complement"
                type="text"
                :value="old('complement')"
            />
            <x-input-error class="mt-2" :messages="$errors->get('complement')" />
        </div>
    </div>

    <!-- Neighborhood Field -->
    <div class="form-group">
        <x-input-label for="neighborhood" :value="'Bairro' . ($required ? ' *' : '')" />
        <x-text-input
            id="neighborhood"
            name="neighborhood"
            type="text"
            :value="old('neighborhood')"
            :required="$required"
        />
        <x-input-error class="mt-2" :messages="$errors->get('neighborhood')" />
    </div>

    <!-- City and State -->
    <div class="form-grid form-grid-2">
        <div>
            <x-input-label for="city" :value="'Cidade' . ($required ? ' *' : '')" />
            <x-text-input
                id="city"
                name="city"
                type="text"
                :value="old('city')"
                :required="$required"
            />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <div>
            <x-input-label for="state" :value="'Estado (UF)' . ($required ? ' *' : '')" />
            <select
                id="state"
                name="state"
                :required="$required"
            >
                <option value="">Selecione...</option>
                <option value="AC" {{ old('state') == 'AC' ? 'selected' : '' }}>AC</option>
                <option value="AL" {{ old('state') == 'AL' ? 'selected' : '' }}>AL</option>
                <option value="AP" {{ old('state') == 'AP' ? 'selected' : '' }}>AP</option>
                <option value="AM" {{ old('state') == 'AM' ? 'selected' : '' }}>AM</option>
                <option value="BA" {{ old('state') == 'BA' ? 'selected' : '' }}>BA</option>
                <option value="CE" {{ old('state') == 'CE' ? 'selected' : '' }}>CE</option>
                <option value="DF" {{ old('state') == 'DF' ? 'selected' : '' }}>DF</option>
                <option value="ES" {{ old('state') == 'ES' ? 'selected' : '' }}>ES</option>
                <option value="GO" {{ old('state') == 'GO' ? 'selected' : '' }}>GO</option>
                <option value="MA" {{ old('state') == 'MA' ? 'selected' : '' }}>MA</option>
                <option value="MT" {{ old('state') == 'MT' ? 'selected' : '' }}>MT</option>
                <option value="MS" {{ old('state') == 'MS' ? 'selected' : '' }}>MS</option>
                <option value="MG" {{ old('state') == 'MG' ? 'selected' : '' }}>MG</option>
                <option value="PA" {{ old('state') == 'PA' ? 'selected' : '' }}>PA</option>
                <option value="PB" {{ old('state') == 'PB' ? 'selected' : '' }}>PB</option>
                <option value="PR" {{ old('state') == 'PR' ? 'selected' : '' }}>PR</option>
                <option value="PE" {{ old('state') == 'PE' ? 'selected' : '' }}>PE</option>
                <option value="PI" {{ old('state') == 'PI' ? 'selected' : '' }}>PI</option>
                <option value="RJ" {{ old('state') == 'RJ' ? 'selected' : '' }}>RJ</option>
                <option value="RN" {{ old('state') == 'RN' ? 'selected' : '' }}>RN</option>
                <option value="RS" {{ old('state') == 'RS' ? 'selected' : '' }}>RS</option>
                <option value="RO" {{ old('state') == 'RO' ? 'selected' : '' }}>RO</option>
                <option value="RR" {{ old('state') == 'RR' ? 'selected' : '' }}>RR</option>
                <option value="SC" {{ old('state') == 'SC' ? 'selected' : '' }}>SC</option>
                <option value="SP" {{ old('state') == 'SP' ? 'selected' : '' }}>SP</option>
                <option value="SE" {{ old('state') == 'SE' ? 'selected' : '' }}>SE</option>
                <option value="TO" {{ old('state') == 'TO' ? 'selected' : '' }}>TO</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('state')" />
        </div>
    </div>

    <!-- Hidden type field -->
    <input type="hidden" name="type" value="{{ $type }}">
</div>

@push('scripts')
<script>
function cepLookup() {
    return {
        cep: '{{ old("postal_code") }}',
        loading: false,
        error: '',

        formatCep() {
            let value = this.cep.replace(/\D/g, '');
            if (value.length > 5) {
                this.cep = value.substring(0, 5) + '-' + value.substring(5, 8);
            } else {
                this.cep = value;
            }
        },

        async searchCep() {
            const cep = this.cep.replace(/\D/g, '');

            if (cep.length !== 8) return;

            this.loading = true;
            this.error = '';

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (data.erro) {
                    this.error = 'CEP não encontrado';
                    return;
                }

                // Fill address fields
                document.querySelector('[name="street"]').value = data.logradouro || '';
                document.querySelector('[name="neighborhood"]').value = data.bairro || '';
                document.querySelector('[name="city"]').value = data.localidade || '';

                // Set state select
                const stateSelect = document.querySelector('[name="state"]');
                if (stateSelect && data.uf) {
                    stateSelect.value = data.uf;
                }

                // Focus on number field
                document.querySelector('[name="number"]')?.focus();
            } catch (error) {
                this.error = 'Erro ao buscar CEP. Tente novamente.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
