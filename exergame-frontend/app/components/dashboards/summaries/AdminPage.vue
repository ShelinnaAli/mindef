<template>
	<div>
		<PartialsSummariesStatCounter />

		<div class="section">
			<div class="section-header">
				<h2 class="section-title">Popular Programmes</h2>
			</div>

			<PartialsProgrammesListCard
				:is-readonly="true"
				:use-data="'popular'"
			/>
		</div>
	</div>
</template>
