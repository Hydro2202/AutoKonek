@extends('layouts.owner')

@section('title', 'Add establishment')
@section('heading', 'Add establishment')
@section('subheading', 'An administrator checks every new listing before it appears to drivers.')

@section('content')
<form method="POST" action="{{ route('owner.establishments.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @include('owner.establishments._form')
    <div class="flex gap-2">
        <button type="submit" class="btn-primary btn-lg">Submit for approval</button>
        <a href="{{ route('owner.establishments.index') }}" class="btn-outline btn-lg">Cancel</a>
    </div>
</form>
@endsection
