/**
 * Vanilla JavaScript Input Masks
 * Replaces @alpinejs/mask dependency (-5 KB)
 *
 * Usage:
 * <input data-mask="cep" name="postal_code">
 * <input data-mask="phone" name="phone">
 * <input data-mask="cpf" name="cpf">
 * <input data-mask="cnpj" name="cnpj">
 */

/**
 * CEP Mask: 00000-000
 */
export function maskCEP(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 8) value = value.substring(0, 8);

    if (value.length > 5) {
        input.value = value.substring(0, 5) + '-' + value.substring(5);
    } else {
        input.value = value;
    }
}

/**
 * Telefone Mask: (00) 00000-0000 ou (00) 0000-0000
 */
export function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.substring(0, 11);

    if (value.length > 10) {
        // Celular: (00) 00000-0000
        input.value = '(' + value.substring(0, 2) + ') ' +
                      value.substring(2, 7) + '-' + value.substring(7);
    } else if (value.length > 6) {
        // Telefone fixo ou celular incompleto
        input.value = '(' + value.substring(0, 2) + ') ' +
                      value.substring(2, 6) + '-' + value.substring(6);
    } else if (value.length > 2) {
        input.value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
    } else if (value.length > 0) {
        input.value = '(' + value;
    }
}

/**
 * CPF Mask: 000.000.000-00
 */
export function maskCPF(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.substring(0, 11);

    if (value.length > 9) {
        input.value = value.substring(0, 3) + '.' +
                      value.substring(3, 6) + '.' +
                      value.substring(6, 9) + '-' + value.substring(9);
    } else if (value.length > 6) {
        input.value = value.substring(0, 3) + '.' +
                      value.substring(3, 6) + '.' + value.substring(6);
    } else if (value.length > 3) {
        input.value = value.substring(0, 3) + '.' + value.substring(3);
    } else {
        input.value = value;
    }
}

/**
 * CNPJ Mask: 00.000.000/0000-00
 */
export function maskCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 14) value = value.substring(0, 14);

    if (value.length > 12) {
        input.value = value.substring(0, 2) + '.' +
                      value.substring(2, 5) + '.' +
                      value.substring(5, 8) + '/' +
                      value.substring(8, 12) + '-' + value.substring(12);
    } else if (value.length > 8) {
        input.value = value.substring(0, 2) + '.' +
                      value.substring(2, 5) + '.' +
                      value.substring(5, 8) + '/' + value.substring(8);
    } else if (value.length > 5) {
        input.value = value.substring(0, 2) + '.' +
                      value.substring(2, 5) + '.' + value.substring(5);
    } else if (value.length > 2) {
        input.value = value.substring(0, 2) + '.' + value.substring(2);
    } else {
        input.value = value;
    }
}

/**
 * Money Mask: R$ 0.000,00
 */
export function maskMoney(input) {
    let value = input.value.replace(/\D/g, '');

    if (value.length === 0) {
        input.value = '';
        return;
    }

    // Convert to float: 1234 -> 12.34
    const floatValue = parseFloat(value) / 100;

    // Format as Brazilian Real
    input.value = floatValue.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Apply masks automatically on DOMContentLoaded
 */
export function initializeMasks() {
    console.log('🎭 Initializing vanilla masks...');

    // Helper function to apply mask on input event
    const applyMask = (selector, maskFunction) => {
        document.querySelectorAll(selector).forEach(input => {
            input.addEventListener('input', () => maskFunction(input));
        });
    };

    // Apply masks
    applyMask('input[data-mask="cep"]', maskCEP);
    applyMask('input[data-mask="phone"]', maskPhone);
    applyMask('input[data-mask="cpf"]', maskCPF);
    applyMask('input[data-mask="cnpj"]', maskCNPJ);
    applyMask('input[data-mask="money"]', maskMoney);

    console.log('✅ Vanilla masks initialized');
}

// Auto-initialize on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMasks);
} else {
    // DOM already loaded
    initializeMasks();
}
