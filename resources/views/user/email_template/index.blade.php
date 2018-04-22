@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <email-template email-templates="{{ json_encode($emailTemplates) }}" item="{{ $emailTemplates->first() }}" url="{{ url('email_template') }}/"></email-template>
@stop

{{-- Scripts --}}
@section('scripts')

@stop