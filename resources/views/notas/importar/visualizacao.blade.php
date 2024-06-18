@extends('layouts.app')

@section('content')
<h1>Importação - Visualização</h1>

<table id="notas-table" class="table table-bordered table-striped table-hover" style="font-size:15px;">
  <thead>
    <tr>
      <th>NºUSP</th>
      <th>Nome</th>
      <th>Curso</th>
      <th>Ano de ingresso</th>
      <th>Média Limpa</th>
      <th>Média Suja</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($alunos as $aluno)
      <tr>
        <td>{{ $aluno['nusp'] }}</td>
        <td>{{ $aluno['nome'] }}</td>
        <td>{{ $aluno['curso'] }}</td>
        <td>{{ $aluno['ingresso'] }}</td>
        <td>{{ $aluno['medias']['limpa'] }}</td>
        <td>{{ $aluno['medias']['suja'] }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

@endsection


@section('javascripts_bottom')
    @parent
    <script>
        $(document).ready(function() {
            $('#notas-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                'csv', 'excel'
                ],
            });
        });
    </script>
@endsection