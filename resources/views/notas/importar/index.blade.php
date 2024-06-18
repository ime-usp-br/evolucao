@extends('layouts.app')

@section('content')
<h1>Importação</h1>

<p>Envie uma lista de alunos em CSV no formato</p>
<table>
  <thead>
    <tr>
      <th>nusp</th>
      <th>nome</th>
      <th>curso</th>
      <th>ingresso</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>12345678</td>
      <td>Joãozinho da Silva</td>
      <td>BMAC</td>
      <td>2024</td>
    </tr>
  </tbody>
</table>

<form action="notas/importar/csv" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="file" name="arquivo" id="arquivo">
  <input type="submit" value="Importar">
</form>

@endsection
