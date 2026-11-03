@extends('layouts.panel')

@section('title', 'Add establishment')
@section('heading', 'Add establishment')
@section('subheading', 'Submit your business for approval. It becomes public once an administrator approves it.')

@section('content')
<form method="POST" action="{{ route('owner.establishments.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-5">
    @csrf
    @include('owner.establishments._form')
    <div class="flex gap-2">
        <button type="submit" class="btn-primary">Submit for approval</button>
        <a href="{{ route('owner.establishments.index') }}" class="btn-outline">Cancel</a>
    </div>
</form>
@endsection
