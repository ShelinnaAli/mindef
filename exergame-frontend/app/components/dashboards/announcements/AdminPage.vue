<template>
	<div>
		<PartialsAnnouncementsForm
			:edit="editAnnouncement"
			@success-submit="refreshAnnouncements"
		/>

    <PartialsAnnouncementsList
      ref="announcementsListRef"
      @edit-announcement="handleEditAnnouncement"
    />

	</div>
</template>

<script setup>
const editAnnouncement = ref(null);
const announcementsListRef = ref(null);

const refreshAnnouncements = async() => {
  announcementsListRef.value?.getAnnouncements();
};

const handleEditAnnouncement = async (item) => {
	if (!item) return;

	editAnnouncement.value = {
		id: item.id,
		title: item.title,
		content: item.content,
		typeId: item.typeId,
		isActive: item.isActive
	};
};
</script>
