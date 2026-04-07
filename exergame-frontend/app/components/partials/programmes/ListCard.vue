<template>
  <div>
    <PartialsDataLoading v-if="isLoading" />
    <PartialsDataError
      v-else-if="error"
      :error="error"
      @retry="getProgrammes"
    />
    <PartialsDataNotFound v-else-if="dataProgrammes.length === 0" />

    <PartialsProgrammesCard
      v-else
      :programmes="dataProgrammes"
      :is-readonly="isReadonly"
    />

  </div>
</template>

<script setup>
const {
  programmes,
  isLoading,
  error,
  fetchPopularProgrammes,
  fetchProgrammes,
} = useProgrammes();

const props = defineProps({
  useData: {
    type: String,
    default: "all", // 'all', 'popular'
  },
  isReadonly: {
    type: Boolean,
    default: false,
  },
});

const getProgrammes = () => {
  if (props.useData === "popular") {
    return fetchPopularProgrammes();
  }
  return fetchProgrammes();
};

const dataProgrammes = computed(() => {
  return programmes.value.map((item) => {
    const firstSchedule = item.schedules[0] || null;

    return {
      ...item,
      schedule: firstSchedule,
    };
  });
});

onMounted(() => getProgrammes());
</script>
