<template>
    <div
        class="min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-200"
    >
        <!-- Header -->
        <header
            class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-300 dark:border-gray-700"
        >
            <div
                class="max-w-6xl mx-auto flex items-center justify-between p-6"
            >
                <!-- Logo / Home -->
                <a href="/" class="flex items-center space-x-2">
                    <h1
                        class="text-3xl font-bold text-gray-800 dark:text-gray-100 hover:text-gray-600 dark:hover:text-gray-300 transition"
                    >
                        Scrabble
                    </h1>
                </a>

                <!-- Navigation -->
                <nav class="flex space-x-6">
                    <a
                        href="/games"
                        class="text-lg font-medium text-gray-700 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-400 transition"
                    >
                        Games
                    </a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto p-6">
            <slot />
        </main>
    </div>
</template>
