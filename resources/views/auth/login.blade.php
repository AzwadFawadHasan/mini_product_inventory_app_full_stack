@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    {{-- We will handle errors with JavaScript --}}
                    <div id="login-error-message" class="alert alert-danger" style="display: none;"></div>

                    <form id="loginForm">
                        {{-- CSRF token is not strictly needed for API calls if using JWT for auth,
                             but good practice if any part of your app still uses web sessions.
                             For pure API interaction, it can be omitted from the JS call. --}}
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                            </div>
                        </div>

                        {{-- "Remember Me" can be handled by JWT expiration or refresh tokens if needed later --}}
                        {{-- <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div> --}}

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add a script section for our JavaScript --}}
@push('scripts')
@push('scripts')
<script>
    

    document.addEventListener('DOMContentLoaded', function () {
       

        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email'); // Get email input
        const passwordInput = document.getElementById('password'); // Get password input
        const errorMessageDiv = document.getElementById('login-error-message');

        if (!loginForm) {
            return;
        }


        if (!emailInput) {
            console.error('[LOGIN SCRIPT] CRITICAL: Email input (id="email") not found!');
        }
        if (!passwordInput) {
            console.error('[LOGIN SCRIPT] CRITICAL: Password input (id="password") not found!');
        }
        if (!errorMessageDiv) {
            console.error('[LOGIN SCRIPT] CRITICAL: Error message div (id="login-error-message") not found!');
        }

        loginForm.addEventListener('submit', async function (event) {
            

            event.preventDefault(); // Attempt to prevent default form submission
   

            errorMessageDiv.style.display = 'none';
            errorMessageDiv.textContent = '';

            const email = emailInput.value;
            const password = passwordInput.value;

           

            if (!email || !password) {
                errorMessageDiv.textContent = 'Email and password are required.';
                errorMessageDiv.style.display = 'block';
                
                return;
            }
            

            try {
                
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });
        
                const data = await response.json();
                

                if (response.ok) {
                   
                    localStorage.setItem('access_token', data.access_token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    window.location.href = '/products';
                } else {
          
                    let errorMsg = 'Login failed. Please check your credentials.';
                    if (data.error) {
                        errorMsg = data.error;
                    } else if (data.message) {
                        errorMsg = data.message;
                    } else if (data.errors) {
                        const errors = Object.values(data.errors).flat();
                        errorMsg = errors.join(' ');
                    }
                    errorMessageDiv.textContent = errorMsg;
                    errorMessageDiv.style.display = 'block';
                }
            } catch (error) {
               
                errorMessageDiv.textContent = 'An unexpected error occurred. Please try again. Check console.';
                errorMessageDiv.style.display = 'block';
            }
           
        });
      
    });
  
</script>
@endpush

@endpush
@endsection
