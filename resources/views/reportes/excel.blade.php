{!! '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<?mso-application progid="Excel.Sheet"?>' !!}
{{-- Hoja de calculo XML nativa de Excel (SpreadsheetML 2003): Excel la abre
     sin la advertencia de "formato y extension no coinciden" que produce el
     truco de servir HTML con extension .xls, y con estilos reales de celda. --}}
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
    <Styles>
        <Style ss:ID="marca">
            <Font ss:Bold="1" ss:Size="20" ss:Color="#39A900"/>
        </Style>
        <Style ss:ID="submarca">
            <Font ss:Bold="1" ss:Size="8" ss:Color="#64748B"/>
        </Style>
        <Style ss:ID="titulo">
            <Font ss:Bold="1" ss:Size="13" ss:Color="#0F172A"/>
        </Style>
        <Style ss:ID="metaEtiqueta">
            <Font ss:Bold="1" ss:Size="10" ss:Color="#334155"/>
        </Style>
        <Style ss:ID="metaValor">
            <Font ss:Size="10" ss:Color="#475569"/>
        </Style>
        <Style ss:ID="encabezado">
            <Font ss:Bold="1" ss:Size="10" ss:Color="#FFFFFF"/>
            <Interior ss:Color="#39A900" ss:Pattern="Solid"/>
            <Alignment ss:Vertical="Center" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#2F8B00"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#2F8B00"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#2F8B00"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#2F8B00"/>
            </Borders>
        </Style>
        <Style ss:ID="celda">
            <Font ss:Size="10" ss:Color="#1E293B"/>
            <Alignment ss:Vertical="Top" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
            </Borders>
        </Style>
        <Style ss:ID="celdaAlterna">
            <Font ss:Size="10" ss:Color="#1E293B"/>
            <Interior ss:Color="#F6FAF3" ss:Pattern="Solid"/>
            <Alignment ss:Vertical="Top" ss:WrapText="1"/>
            <Borders>
                <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
                <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
                <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
                <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D7DFD2"/>
            </Borders>
        </Style>
    </Styles>
    <Worksheet ss:Name="Reporte">
        <Table>
            @foreach($encabezados as $indice => $h)
                <Column ss:AutoFitWidth="0" ss:Width="{{ $indice === 0 ? 32 : ($indice >= count($encabezados) - 2 ? 160 : 110) }}"/>
            @endforeach

            {{-- Marca institucional --}}
            <Row ss:Height="26"><Cell ss:StyleID="marca" ss:MergeAcross="{{ count($encabezados) - 1 }}"><Data ss:Type="String">GEVLA</Data></Cell></Row>
            <Row><Cell ss:StyleID="submarca" ss:MergeAcross="{{ count($encabezados) - 1 }}"><Data ss:Type="String">SENA · GESTIÓN DISCIPLINARIA Y FORMATIVA</Data></Cell></Row>
            <Row ss:Height="20"><Cell ss:StyleID="titulo" ss:MergeAcross="{{ count($encabezados) - 1 }}"><Data ss:Type="String">{{ $titulo }}</Data></Cell></Row>

            {{-- Metadatos del reporte --}}
            @foreach($meta as $m)
                <Row>
                    <Cell ss:StyleID="metaEtiqueta"><Data ss:Type="String">{{ $m['label'] }}:</Data></Cell>
                    <Cell ss:StyleID="metaValor" ss:MergeAcross="{{ count($encabezados) - 2 }}"><Data ss:Type="String">{{ $m['value'] }}</Data></Cell>
                </Row>
            @endforeach
            <Row/>

            {{-- Encabezados de la tabla --}}
            <Row ss:Height="24">
                @foreach($encabezados as $h)
                    <Cell ss:StyleID="encabezado"><Data ss:Type="String">{{ $h }}</Data></Cell>
                @endforeach
            </Row>

            {{-- Filas de datos (con sombreado alterno) --}}
            @forelse($filas as $fila)
                <Row>
                    @foreach($fila as $celda)
                        <Cell ss:StyleID="{{ $loop->parent->iteration % 2 === 0 ? 'celdaAlterna' : 'celda' }}"><Data ss:Type="{{ is_numeric($celda) ? 'Number' : 'String' }}">{{ $celda }}</Data></Cell>
                    @endforeach
                </Row>
            @empty
                <Row><Cell ss:StyleID="celda" ss:MergeAcross="{{ count($encabezados) - 1 }}"><Data ss:Type="String">Sin registros.</Data></Cell></Row>
            @endforelse
        </Table>
        <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
            <PageSetup>
                <Layout x:Orientation="Landscape"/>
            </PageSetup>
        </WorksheetOptions>
    </Worksheet>
</Workbook>
