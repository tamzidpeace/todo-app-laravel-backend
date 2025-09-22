<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Todo App</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Todo App</h1>
            
            <!-- Add Todo Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Add New Todo</h2>
                <form id="addTodoForm" class="flex gap-2">
                    <input 
                        type="text" 
                        id="todoTitle" 
                        placeholder="Enter todo title..." 
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                    <button 
                        type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
                    >
                        Add Todo
                    </button>
                </form>
            </div>

            <!-- Todos List -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Your Todos</h2>
                <div id="todosList" class="space-y-3">
                    <!-- Todos will be loaded here -->
                    <p class="text-center text-gray-500 py-4">Loading todos...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // GraphQL endpoint
        const GRAPHQL_ENDPOINT = '/graphql';
        
        // Function to make GraphQL requests
        async function graphqlQuery(query, variables = {}) {
            try {
                const response = await axios.post(GRAPHQL_ENDPOINT, {
                    query,
                    variables
                });
                return response.data;
            } catch (error) {
                console.error('GraphQL Error:', error);
                throw error;
            }
        }
        
        // Load all todos
        async function loadTodos() {
            const query = `
                query {
                    todos {
                        id
                        title
                        completed
                        created_at
                    }
                }
            `;
            
            try {
                const response = await graphqlQuery(query);
                const todos = response.data.todos;
                displayTodos(todos);
            } catch (error) {
                document.getElementById('todosList').innerHTML = 
                    '<p class="text-center text-red-500 py-4">Failed to load todos</p>';
            }
        }
        
        // Display todos in the UI
        function displayTodos(todos) {
            const todosList = document.getElementById('todosList');
            
            if (todos.length === 0) {
                todosList.innerHTML = '<p class="text-center text-gray-500 py-4">No todos found</p>';
                return;
            }
            
            todosList.innerHTML = todos.map(todo => `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg ${todo.completed ? 'bg-green-50' : ''}">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            ${todo.completed ? 'checked' : ''}
                            onchange="toggleTodo(${todo.id}, this.checked)"
                            class="h-5 w-5 text-blue-500 rounded focus:ring-blue-400"
                        >
                        <span class="ml-3 text-gray-700 ${todo.completed ? 'line-through text-gray-500' : ''}">
                            ${todo.title}
                        </span>
                    </div>
                    <button 
                        onclick="deleteTodo(${todo.id})"
                        class="text-red-500 hover:text-red-700 font-medium"
                    >
                        Delete
                    </button>
                </div>
            `).join('');
        }
        
        // Add a new todo
        async function addTodo(title) {
            const mutation = `
                mutation($title: String!) {
                    createTodo(title: $title) {
                        id
                        title
                        completed
                    }
                }
            `;
            
            try {
                await graphqlQuery(mutation, { title });
                document.getElementById('todoTitle').value = '';
                loadTodos(); // Reload the todos list
            } catch (error) {
                alert('Failed to add todo');
            }
        }
        
        // Toggle todo completion status
        async function toggleTodo(id, completed) {
            const mutation = `
                mutation($id: ID!, $completed: Boolean) {
                    updateTodo(id: $id, completed: $completed) {
                        id
                        title
                        completed
                    }
                }
            `;
            
            try {
                await graphqlQuery(mutation, { id, completed });
                loadTodos(); // Reload the todos list
            } catch (error) {
                alert('Failed to update todo');
            }
        }
        
        // Delete a todo
        async function deleteTodo(id) {
            if (!confirm('Are you sure you want to delete this todo?')) {
                return;
            }
            
            const mutation = `
                mutation($id: ID!) {
                    deleteTodo(id: $id)
                }
            `;
            
            try {
                const response = await graphqlQuery(mutation, { id });
                
                // Check if there was an error in the response
                if (response.errors) {
                    alert('Failed to delete todo: ' + response.errors[0].message);
                    return;
                }
                
                loadTodos(); // Reload the todos list
            } catch (error) {
                alert('Failed to delete todo: ' + (error.response?.data?.errors?.[0]?.message || error.message));
            }
        }
        
        // Handle form submission
        document.getElementById('addTodoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const title = document.getElementById('todoTitle').value.trim();
            if (title) {
                addTodo(title);
            }
        });
        
        // Load todos when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadTodos();
        });
    </script>
</body>
</html>