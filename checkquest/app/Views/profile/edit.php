<?php include_once __DIR__ . '/../partials/header.php'; ?>

<div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded-2xl shadow-lg">
    <h1 class="text-2xl font-bold mb-4">Edit Profile</h1>

    <form method="POST" action="/profile/edit" class="space-y-4">
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">About Me</label>
            <textarea id="description" name="description" rows="6"
                      class="mt-1 block w-full p-2 border border-gray-300 rounded"
                      placeholder="Write something about yourself..."><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
        </div>

        <div class="flex justify-between">
            <a href="/profile" class="text-sm text-gray-500 hover:underline">Cancel</a>
            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Save</button>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
