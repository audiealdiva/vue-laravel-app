<template>
  <nav class="navbar navbar-expand-lg navbar-light border-bottom" :style="{ backgroundColor: 'var(--primary-color)', height: '60px', 'border-bottom': 'none' }">
    <div class="container-fluid">
      <button class="btn btn-sm btn-light" @click="$emit('toggle-sidebar')">
        <i class="bi bi-list text-white"></i>
      </button>
      <button class="btn btn-sm btn-light ms-3" @click="$emit('logout')" title="Logout">
        <i class="bi bi-box-arrow-right text-white"></i>
      </button>
    </div>
  </nav>
</template>

<style scoped>
button {
  background: none;
  border: none;
  font-size: 1.5rem;
}

button i {
  transition: transform 0.3s;
}

button:hover i {
  transform: scale(1.2);
}
</style>