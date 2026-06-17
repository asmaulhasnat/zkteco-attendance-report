 @foreach($employees as $e)
    <option
        value="{{ $e->id }}"
        {{ old('employee') == $e->id ? 'selected' : '' }}
    >
    {{$e->emp_code}} : {{ $e->first_name }} {{ $e->last_name }}
    </option>
@endforeach