<template>
  <head>

  </head>
  <body>
    <section>
      <header class="grid grid-cols-2 items-center gap-2 p-10 bg-gray-300 border border-b-4 border-gray-500">
        <div class="flex">
          <a href="/" class="flex">
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">Scrabble</h1>
          </a>
        </div>
        <div class="flex">
          <a href="/login" class="text-2xl font-semibold leading-6 text-gray-900">Games</a>
        </div>
      </header>
    </section>
    <main class="p-10">
      <slot />
    </main>
    
  </body>
  
</template>