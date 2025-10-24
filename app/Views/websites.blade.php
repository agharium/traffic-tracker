@extends('layouts.base')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">My Websites</h1>
            <p class="text-gray-600 mt-1">Manage your tracked websites and API keys</p>
        </div>
        <button class="btn btn-primary" onclick="showAddModal()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Website
        </button>
    </div>

    <!-- Websites Grid -->
    @if(count($websites) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($websites as $website)
                <div class="card bg-base-100 shadow-lg" id="website-{{ $website->getId() }}">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="card-title text-lg">{{ $website->getName() }}</h2>
                                <p class="text-gray-600 text-sm">{{ $website->getDomain() }}</p>
                            </div>
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li><a onclick="regenerateApiKey({{ $website->getId() }})">🔄 Regenerate API Key</a></li>
                                    <li><a onclick="viewStats({{ $website->getId() }})">📊 View Stats</a></li>
                                    <li><a onclick="deleteWebsite({{ $website->getId() }})" class="text-error">🗑️ Delete</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="label">
                                <span class="label-text font-semibold">API Key</span>
                                <span class="label-text-alt">
                                    <button class="btn btn-xs" onclick="copyApiKey('{{ $website->getApiKey() }}')">📋 Copy</button>
                                </span>
                            </label>
                            <input type="text" class="input input-bordered input-sm w-full font-mono text-xs" 
                                   value="{{ $website->getApiKey() }}" readonly>
                        </div>

                        <div class="mt-4">
                            <label class="label">
                                <span class="label-text font-semibold">Tracking Script</span>
                                <span class="label-text-alt">
                                    <button class="btn btn-xs" onclick="copyScript('{{ $website->getApiKey() }}')">📋 Copy</button>
                                </span>
                            </label>
                            <textarea class="textarea textarea-bordered textarea-sm w-full font-mono text-xs" rows="3" readonly>&lt;script src="//{{ $_SERVER['HTTP_HOST'] ?? 'localhost:8080' }}/api/tracking-script?key={{ $website->getApiKey() }}"&gt;&lt;/script&gt;</textarea>
                        </div>

                        <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
                            <span>Created: {{ $website->getCreatedAt()->format('M j, Y') }}</span>
                            <div class="badge badge-success badge-sm">Active</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="mb-4">
                <svg class="mx-auto h-12 w-12 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-base-content mb-2">No websites yet</h3>
            <p class="text-base-content/70 mb-4">Get started by adding your first website to track.</p>
            <button class="btn btn-primary" onclick="showAddModal()">Add Your First Website</button>
        </div>
    @endif
</div>

<!-- Add Website Modal -->
<dialog id="addWebsiteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Add New Website</h3>
        <form id="addWebsiteForm" onsubmit="addWebsite(event)">
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">Website Name</span>
                </label>
                <input type="text" name="name" placeholder="e.g., My Blog" class="input input-bordered" required>
            </div>
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">Domain</span>
                </label>
                <input type="text" name="domain" placeholder="e.g., example.com" class="input input-bordered" required>
                <label class="label">
                    <span class="label-text-alt">Don't include http:// or www.</span>
                </label>
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Website</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Success Toast -->
<div id="successToast" class="toast toast-top toast-end hidden">
    <div class="alert alert-success">
        <span id="successMessage"></span>
    </div>
</div>

<!-- Error Toast -->
<div id="errorToast" class="toast toast-top toast-end hidden">
    <div class="alert alert-error">
        <span id="errorMessage"></span>
    </div>
</div>

<!-- Confirmation Modal -->
<dialog id="confirmModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg" id="confirmTitle">Confirm Action</h3>
        <p class="py-4" id="confirmMessage">Are you sure you want to proceed?</p>
        <div class="modal-action">
            <button class="btn" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn btn-error" id="confirmButton" onclick="confirmAction()">Confirm</button>
        </div>
    </div>
</dialog>

<script>
const currentHost = window.location.host;
let currentAction = null;
let currentActionData = null;

function showAddModal() {
    document.getElementById('addWebsiteModal').showModal();
}

function closeAddModal() {
    document.getElementById('addWebsiteModal').close();
    document.getElementById('addWebsiteForm').reset();
}

function showToast(message, type = 'success') {
    const toastId = type === 'success' ? 'successToast' : 'errorToast';
    const messageId = type === 'success' ? 'successMessage' : 'errorMessage';
    
    document.getElementById(messageId).textContent = message;
    document.getElementById(toastId).classList.remove('hidden');
    
    setTimeout(() => {
        document.getElementById(toastId).classList.add('hidden');
    }, 4000);
}

function showConfirmDialog(title, message, action, data) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMessage').textContent = message;
    currentAction = action;
    currentActionData = data;
    document.getElementById('confirmModal').showModal();
}

function closeConfirmModal() {
    document.getElementById('confirmModal').close();
    currentAction = null;
    currentActionData = null;
}

function confirmAction() {
    if (currentAction && currentActionData) {
        currentAction(currentActionData);
    }
    closeConfirmModal();
}

function addWebsite(event) {
    event.preventDefault();
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Adding...';
    submitBtn.disabled = true;
    
    const formData = new FormData(event.target);
    formData.append('ajax', '1');
    
    fetch('/websites', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAddModal();
            showToast('Website added successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function copyApiKey(apiKey) {
    navigator.clipboard.writeText(apiKey).then(() => {
        showToast('API key copied to clipboard!');
    }).catch(() => {
        showToast('Failed to copy API key', 'error');
    });
}

function copyScript(apiKey) {
    const script = '<script src="//' + currentHost + '/api/tracking-script?key=' + apiKey + '"><\/script>';
    navigator.clipboard.writeText(script).then(() => {
        showToast('Tracking script copied to clipboard!');
    }).catch(() => {
        showToast('Failed to copy tracking script', 'error');
    });
}

function regenerateApiKey(websiteId) {
    showConfirmDialog(
        'Regenerate API Key',
        'Are you sure? This will invalidate the current API key and you will need to update your tracking script.',
        performRegenerateApiKey,
        websiteId
    );
}

function performRegenerateApiKey(websiteId) {
    // Find and update all regenerate buttons for this website
    const regenerateBtn = document.querySelector(`button[onclick="regenerateApiKey(${websiteId})"]`);
    let originalText = '';
    
    if (regenerateBtn) {
        originalText = regenerateBtn.innerHTML;
        regenerateBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Regenerating...';
        regenerateBtn.disabled = true;
    }
    
    const formData = new FormData();
    formData.append('website_id', websiteId);
    
    fetch('/websites/regenerate-key', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('API key regenerated successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        if (regenerateBtn) {
            regenerateBtn.innerHTML = originalText;
            regenerateBtn.disabled = false;
        }
    });
}

function deleteWebsite(websiteId) {
    showConfirmDialog(
        'Delete Website',
        'Are you sure? This will permanently delete the website and all its tracking data.',
        performDeleteWebsite,
        websiteId
    );
}

function performDeleteWebsite(websiteId) {
    // Find and update the delete button for this website
    const deleteBtn = document.querySelector(`button[onclick="deleteWebsite(${websiteId})"]`);
    let originalText = '';
    
    if (deleteBtn) {
        originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Deleting...';
        deleteBtn.disabled = true;
    }
    
    const formData = new FormData();
    formData.append('website_id', websiteId);
    
    fetch('/websites/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Website deleted successfully!');
            document.getElementById('website-' + websiteId).remove();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state (if not deleted)
        if (deleteBtn && document.body.contains(deleteBtn)) {
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    });
}

function viewStats(websiteId) {
    window.location.href = '/dashboard?website=' + websiteId;
}
</script>
@endsection
