<div class="min-h-[80vh] flex items-center justify-center">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title justify-center text-2xl mb-6">Login</h2>
            
            @if(isset($error))
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endif

            <form hx-post="/login" hx-target="#app" hx-swap="innerHTML">
                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text">Username or Email</span>
                    </label>
                    <input type="text" name="login" placeholder="Enter your username or email" class="input input-bordered w-full" required>
                </div>

                <div class="form-control w-full mb-6">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="password-partial" name="password" placeholder="Enter your password" class="input input-bordered w-full pr-12" required>
                        <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-600 hover:text-gray-800" onclick="togglePassword('password-partial')">
                            <svg id="password-partial-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="password-partial-eye-off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-control">
                    <button type="submit" class="btn btn-primary w-full">Login</button>
                </div>
            </form>

            <div class="divider">OR</div>
            
            <div class="text-center">
                <span>Don't have an account? </span>
                <a href="/register" class="link link-primary" hx-push-url="true">Register here</a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(inputId + '-eye');
    const eyeOffIcon = document.getElementById(inputId + '-eye-off');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
    }
}
</script>
