<template>
  <div class="mb-4 card-border">
    <BCardHeader>
      <h5 class="mb-0">
        Endereço
      </h5>
    </BCardHeader>
    <BCardBody>
      <BRow>
        <BCol
          lg="2"
          md="3"
          class="mb-3"
        >
          <label for="zip_code">CEP
            <span class="text-danger">*</span>
          </label>
          <BFormInput
            id="zip_code"
            v-maska="'##.###-###'"
            name="zip_code"
            required
            type="text"
            placeholder="CEP"
            :value="formData.zipCode"
            :state="getFieldState('address.zipCode')"
            aria-describedby="address-zipCode-error-feedback"
            @keyup="$emit('set-address')"
            @input="updateField('zipCode', $event)"
          />
          <BFormInvalidFeedback
            v-if="getFieldError('address.zipCode')"
            id="address-zipCode-error-feedback"
          >
            {{ getFieldError('address.zipCode') }}
          </BFormInvalidFeedback>
        </BCol>
      </BRow>

      <BRow>
        <BCol
          lg="1"
          md="3"
          class="mb-3"
        >
          <label for="state">Estado
            <span class="text-danger">*</span>
          </label>
          <BFormInput
            id="state"
            type="text"
            name="state"
            placeholder="UF"
            :value="formData.uf"
            required
            :state="getFieldState('address.uf')"
            aria-describedby="address-uf-error-feedback"
            @input="updateField('uf', $event)"
          />
          <BFormInvalidFeedback
            v-if="getFieldError('address.uf')"
            id="address-uf-error-feedback"
          >
            {{ getFieldError('address.uf') }}
          </BFormInvalidFeedback>
        </BCol>

        <BCol
          lg="2"
          md="3"
          class="mb-3"
        >
          <label for="city">Cidade
            <span class="text-danger">*</span>
          </label>
          <BFormInput
            id="city"
            type="text"
            name="city"
            placeholder="Cidade"
            :value="formData.city"
            required
            :state="getFieldState('address.city')"
            aria-describedby="address-city-error-feedback"
            @input="updateField('city', $event)"
          />
          <BFormInvalidFeedback
            v-if="getFieldError('address.city')"
            id="address-city-error-feedback"
          >
            {{ getFieldError('address.city') }}
          </BFormInvalidFeedback>
        </BCol>

        <BCol
          lg="4"
          md="6"
          class="mb-3"
        >
          <label for="neighborhood">Bairro
            <span class="text-danger">*</span>
          </label>
          <BFormInput
            id="neighborhood"
            type="text"
            name="neighborhood"
            placeholder="Bairro"
            :value="formData.neighborhood"
            required
            :state="getFieldState('address.neighborhood')"
            aria-describedby="address-neighborhood-error-feedback"
            @input="updateField('neighborhood', $event)"
          />
          <BFormInvalidFeedback
            v-if="getFieldError('address.neighborhood')"
            id="address-neighborhood-error-feedback"
          >
            {{ getFieldError('address.neighborhood') }}
          </BFormInvalidFeedback>
        </BCol>

        <BCol
          md="5"
          class="mb-3"
        >
          <label for="address">Endereço
            <span class="text-danger">*</span>
          </label>
          <BFormInput
            id="address"
            type="text"
            name="address"
            placeholder="Endereço"
            :value="formData.address"
            required
            :state="getFieldState('address.address')"
            aria-describedby="address-address-error-feedback"
            @input="updateField('address', $event)"
          />
          <BFormInvalidFeedback
            v-if="getFieldError('address.address')"
            id="address-address-error-feedback"
          >
            {{ getFieldError('address.address') }}
          </BFormInvalidFeedback>
        </BCol>

        <BCol
          lg="1"
          md="2"
          class="mb-3"
        >
          <label for="number">Número</label>
          <BFormInput
            id="number"
            type="text"
            name="number"
            placeholder="Número"
            :value="formData.number"
            :state="getFieldState('address.number')"
            aria-describedby="address-number-error-feedback"
            @input="updateField('number', $event)"
          />
          <BFormInvalidFeedback
            v-if="getFieldError('address.number')"
            id="address-number-error-feedback"
          >
            {{ getFieldError('address.number') }}
          </BFormInvalidFeedback>
        </BCol>

        <BCol
          md="5"
          class="mb-3"
        >
          <label for="complement">Complemento</label>
          <BFormInput
            id="complement"
            type="text"
            name="complement"
            placeholder="Complemento"
            :value="formData.complement"
            :state="getFieldState('address.complement')"
            aria-describedby="address-complement-error-feedback"
            @input="updateField('complement', $event)"
          />
          <BFormInvalidFeedback
            v-if="getFieldError('address.complement')"
            id="address-complement-error-feedback"
          >
            {{ getFieldError('address.complement') }}
          </BFormInvalidFeedback>
        </BCol>
      </BRow>
    </BCardBody>
  </div>
</template>

<script setup>
import {defineProps, defineEmits} from 'vue';

const props = defineProps({
    formData: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:formData', 'set-address']);

// Helper functions for validation styling
function getFieldError(fieldName) {
    return props.errors[fieldName]?.[0] || null;
}

function getFieldState(fieldName) {
    if (props.errors[fieldName]) {
        return false;
    }
    return null;
}

// Função para atualizar campos específicos
function updateField(fieldName, value) {
    const newData = { ...props.formData };
    newData[fieldName] = value;
    emit('update:formData', newData);
}
</script>