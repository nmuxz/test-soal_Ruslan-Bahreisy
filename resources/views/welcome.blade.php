<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Mini Task Management System</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <style>
            body { font-family: 'Inter', sans-serif; }
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="bg-gray-50 text-gray-800 antialiased min-h-screen">
        <div class="max-w-3xl mx-auto px-4 py-12" x-data="taskManager()" x-init="fetchTasks()">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <!-- Header -->
                <div class="px-5 py-5 sm:px-8 sm:py-6 bg-indigo-600">
                    <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Mini Task Management</h1>
                    <p class="text-indigo-100 mt-1 text-xs sm:text-sm">Kelola daftar pekerjaan harian Anda</p>
                </div>

                <div class="p-5 sm:p-8">
                    <!-- Form Tambah Task -->
                    <form @submit.prevent="addTask" class="mb-8">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input 
                                type="text" 
                                x-model="newTaskTitle" 
                                placeholder="Apa yang ingin Anda kerjakan?" 
                                class="flex-1 w-full rounded-lg border-gray-300 border px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors shadow-sm"
                                :disabled="isLoading"
                            >
                            <button 
                                type="submit" 
                                class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 flex items-center justify-center sm:min-w-[140px]"
                                :disabled="isLoading || !newTaskTitle.trim()"
                            >
                                <span x-show="!isLoading">Tambah Task</span>
                                <span x-show="isLoading" x-cloak>
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <p x-show="error" x-text="error" class="text-red-500 text-sm mt-2" x-cloak></p>
                    </form>

                    <!-- Filter & Stats -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0 mb-6 border-b border-gray-100 pb-4">
                        <div class="flex flex-wrap gap-2 bg-gray-100 p-1 rounded-lg w-full sm:w-auto">
                            <button @click="filter = 'all'" :class="{'bg-white shadow-sm font-medium': filter === 'all'}" class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 rounded-md text-xs sm:text-sm transition-all text-gray-700 border border-transparent" :class="filter === 'all' ? 'border-gray-200' : ''">Semua</button>
                            <button @click="filter = 'pending'" :class="{'bg-white shadow-sm font-medium': filter === 'pending'}" class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 rounded-md text-xs sm:text-sm transition-all text-gray-700 border border-transparent" :class="filter === 'pending' ? 'border-gray-200' : ''">Pending</button>
                            <button @click="filter = 'completed'" :class="{'bg-white shadow-sm font-medium': filter === 'completed'}" class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 rounded-md text-xs sm:text-sm transition-all text-gray-700 border border-transparent" :class="filter === 'completed' ? 'border-gray-200' : ''">Selesai</button>
                        </div>
                        <div class="text-sm text-gray-500 w-full sm:w-auto text-right sm:text-left">
                            Total: <span class="font-semibold text-gray-700" x-text="filteredTasks.length"></span>
                        </div>
                    </div>

                    <!-- Task List -->
                    <div class="space-y-3">
                        <template x-if="tasksLoading">
                            <div class="flex justify-center py-8">
                                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </template>

                        <template x-if="!tasksLoading && filteredTasks.length === 0">
                            <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                <p class="text-gray-500">Tidak ada task yang ditemukan.</p>
                            </div>
                        </template>

                        <template x-for="task in filteredTasks" :key="task.id">
                            <div class="group flex items-start sm:items-center justify-between p-3 sm:p-4 bg-white border border-gray-100 rounded-lg shadow-sm hover:shadow-md transition-all">
                                <div class="flex items-start sm:items-center gap-3 sm:gap-4 flex-1">
                                    <div class="pt-1 sm:pt-0">
                                        <input 
                                            type="checkbox" 
                                            class="w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 cursor-pointer transition-colors"
                                            :checked="task.status === 'completed'"
                                            @change="toggleStatus(task)"
                                        >
                                    </div>
                                    <div class="flex flex-col flex-1 pr-2 break-words">
                                        <span 
                                            class="font-medium transition-all text-sm sm:text-base leading-tight sm:leading-normal"
                                            :class="task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-800'"
                                            x-text="task.title"
                                        ></span>
                                        <span class="text-[11px] sm:text-xs text-gray-400 mt-1 sm:mt-0" x-text="formatDate(task.created_at)"></span>
                                    </div>
                                </div>
                                <button 
                                    @click="deleteTask(task.id)"
                                    class="text-red-400 hover:text-red-600 p-1.5 sm:p-2 rounded-full hover:bg-red-50 transition-colors focus:outline-none opacity-100 sm:opacity-0 sm:group-hover:opacity-100 flex-shrink-0"
                                    title="Hapus Task"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function taskManager() {
                return {
                    tasks: [],
                    newTaskTitle: '',
                    filter: 'all',
                    isLoading: false,
                    tasksLoading: true,
                    error: null,

                    get filteredTasks() {
                        if (this.filter === 'all') return this.tasks;
                        return this.tasks.filter(t => t.status === this.filter);
                    },

                    async fetchTasks() {
                        this.tasksLoading = true;
                        try {
                            const response = await fetch('/api/tasks');
                            if (!response.ok) throw new Error('Gagal mengambil data task');
                            this.tasks = await response.json();
                        } catch (err) {
                            console.error(err);
                            this.error = 'Koneksi ke server bermasalah.';
                        } finally {
                            this.tasksLoading = false;
                        }
                    },

                    async addTask() {
                        if (!this.newTaskTitle.trim()) return;
                        
                        this.isLoading = true;
                        this.error = null;
                        
                        try {
                            const response = await fetch('/api/tasks', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ title: this.newTaskTitle })
                            });
                            
                            if (!response.ok) {
                                const data = await response.json();
                                throw new Error(data.message || 'Gagal menambahkan task');
                            }
                            
                            const newTask = await response.json();
                            this.tasks.unshift(newTask); // Add to beginning (latest first)
                            this.newTaskTitle = '';
                        } catch (err) {
                            console.error(err);
                            this.error = err.message;
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    async toggleStatus(task) {
                        const newStatus = task.status === 'completed' ? 'pending' : 'completed';
                        
                        // Optimistic update
                        const originalStatus = task.status;
                        task.status = newStatus;
                        
                        try {
                            const response = await fetch(`/api/tasks/${task.id}`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ status: newStatus })
                            });
                            
                            if (!response.ok) throw new Error('Gagal memperbarui status');
                        } catch (err) {
                            console.error(err);
                            // Revert on failure
                            task.status = originalStatus;
                            alert('Gagal memperbarui status task.');
                        }
                    },

                    async deleteTask(id) {
                        if (!confirm('Apakah Anda yakin ingin menghapus task ini?')) return;
                        
                        // Optimistic update
                        const originalTasks = [...this.tasks];
                        this.tasks = this.tasks.filter(t => t.id !== id);
                        
                        try {
                            const response = await fetch(`/api/tasks/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (!response.ok) throw new Error('Gagal menghapus task');
                        } catch (err) {
                            console.error(err);
                            // Revert on failure
                            this.tasks = originalTasks;
                            alert('Gagal menghapus task.');
                        }
                    },
                    
                    formatDate(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        return new Intl.DateTimeFormat('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }).format(date);
                    }
                }
            }
        </script>
    </body>
</html>
