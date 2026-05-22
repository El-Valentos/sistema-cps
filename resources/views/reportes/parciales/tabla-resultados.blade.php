<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @foreach($headers as $header)
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($rows as $row)
            <tr class="hover:bg-gray-50">
                @foreach($row as $cell)
                    <td class="px-4 py-2 text-sm">{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($headers) }}" class="px-4 py-8 text-center text-gray-500">
                    No hay datos para mostrar
                </td>
            </tr>
            @endforelse
        </tbody>
        @if(isset($totals))
        <tfoot class="bg-gray-50 font-bold">
            <tr>
                @foreach($totals as $total)
                    <td class="px-4 py-2 text-sm">{{ $total }}</td>
                @endforeach
            </tr>
        </tfoot>
        @endif
    </table>
</div>