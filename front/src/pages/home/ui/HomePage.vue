<script setup lang="ts">
import { shiftsIcon } from '@/shared/ui'
</script>

<template>
  <section class="home">
    <article class="card">
      <span class="mark" aria-hidden="true">
        <img :src="shiftsIcon" alt="" width="24" height="24" />
      </span>
      <h1>Здравствуйте!</h1>
      <p>
        Добро пожаловать в CRM. Здесь можно создавать, редактировать и публиковать графики смен по точкам
        региона.
      </p>
      <RouterLink class="go" to="/schedules">К графикам</RouterLink>
    </article>
  </section>
</template>

<style scoped>
.home {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 60px);
}

.card {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: min(440px, 100%);
  padding: 40px 32px;
  border-radius: 16px;
  background: #fff;
  text-align: center;
}

.mark {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
  border-radius: 12px;
  background: #e8edfc;
}

h1 {
  margin: 0;
  font-size: 32px;
  font-weight: 500;
  line-height: 32px;
  letter-spacing: -1px;
}

p {
  margin: 12px 0 0;
  color: #737378;
  font-size: 16px;
  font-weight: 500;
  line-height: 24px;
}

.go {
  margin-top: 24px;
  padding: 12px 16px;
  border-radius: 8px;
  background: #5b7dec;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  line-height: 24px;
}

.go:hover {
  background: #1c4ae5;
}

.go:active {
  background: #163bb7;
}

@media (max-width: 1199.98px) {
  .home {
    min-height: calc(100vh - 96px);
    padding: 16px 0;
  }

  .card {
    padding: 32px 20px;
  }
}
</style>
