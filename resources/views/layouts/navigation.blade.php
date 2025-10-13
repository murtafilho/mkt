<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
 <div class="container">
 <!-- Logo -->
 <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
 <x-application-logo class="me-2" style="height: 2rem;" />
 <span class="fw-bold text-dark">{{ config('app.name') }}</span>
 </a>

 <!-- Mobile toggle -->
 <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
 <span class="navbar-toggler-icon"></span>
 </button>

 <!-- Navigation Links -->
 <div class="collapse navbar-collapse" id="navbarNav">
 <ul class="navbar-nav me-auto">
 <li class="nav-item">
 <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
 {{ __('Dashboard') }}
 </a>
 </li>
 </ul>

 <!-- User Dropdown -->
 <div class="dropdown">
 <button class="btn btn-link dropdown-toggle d-flex align-items-center text-decoration-none" 
         type="button" 
         data-bs-toggle="dropdown">
 <span class="me-2">{{ Auth::user()->name }}</span>
 <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
 </svg>
 </button>
 <ul class="dropdown-menu dropdown-menu-end">

 <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
 <li><a class="dropdown-item" href="{{ route('customer.orders.index') }}">Meus Pedidos</a></li>
 <li><hr class="dropdown-divider"></li>
 <li>
 <form method="POST" action="{{ route('logout') }}">
 @csrf
 <button type="submit" class="dropdown-item text-danger">
 {{ __('Log Out') }}
 </button>
 </form>
 </li>
 </ul>
 </div>
 </div>
 </div>
</nav>

