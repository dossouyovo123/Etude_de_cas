<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Demandes de l'usager {{ $npi }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; color: #222; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Demandes de l'usager {{ $npi }}</h1>
    @if ($demandes->isEmpty())
        <p>Aucune demande pour ce NPI.</p>
    @else
        <table>
            <thead>
                <tr><th>N°</th><th>Type d'acte</th><th>Copies</th><th>Statut</th><th>Motif de rejet</th><th>Date</th></tr>
            </thead>
            <tbody>
            @foreach ($demandes as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->type_acte->value }}</td>
                    <td>{{ $d->nombre_copies }}</td>
                    <td>{{ $d->statut->value }}</td>
                    <td>{{ $d->motif_rejet ?? '-' }}</td>
                    <td>{{ $d->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $demandes->links() }}
    @endif
</body>
</html>