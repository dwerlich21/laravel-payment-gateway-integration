<script>
export default {
    components: {},
    props: {
        title: {
            type: String,
            default: "",
        },
        items: {
            type: Array,
            default: () => {
                return [];
            },
        },
    },
};
</script>

<template>
  <b-row>
    <b-col cols="12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between">
        <h4 class="mb-sm-0">
          {{ title }}
        </h4>

        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li
              v-for="(item, index) in items"
              :key="index"
              class="breadcrumb-item"
              :class="{ active: item.active }"
            >
              <span
                v-if="!item.href"
                href="javascript: void(0);"
              >{{ item.text }}</span>
              <router-link
                v-else
                :to="item.href"
              >
                <span>{{ item.text }}</span>
              </router-link>
            </li>
          </ol>
        </div>
      </div>
    </b-col>
  </b-row>
</template>
