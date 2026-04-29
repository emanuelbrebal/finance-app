import { useCallback, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Upload, FileUp, AlertCircle } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useAccounts } from '@/hooks/queries/useAccounts'
import { useUploadImport } from '@/hooks/queries/useImports'

const IMPORTERS = [
  { id: 'auto', label: 'Detectar automaticamente' },
  { id: 'ofx', label: 'OFX (Nubank, Itaú, Bradesco e outros)' },
  { id: 'nubank_csv', label: 'CSV Nubank — Conta Corrente' },
  { id: 'nubank_card_csv', label: 'CSV Nubank — Cartão de Crédito' },
  { id: 'generic_csv', label: 'CSV Genérico' },
]

export default function ImportUploadPage() {
  const navigate = useNavigate()
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [dragOver, setDragOver] = useState(false)
  const [selectedFile, setSelectedFile] = useState<File | null>(null)
  const [accountId, setAccountId] = useState<string>('')
  const [importerHint, setImporterHint] = useState('auto')
  const [error, setError] = useState<string | null>(null)

  const { data: accounts } = useAccounts()
  const upload = useUploadImport()

  const handleFile = (file: File) => {
    const ext = file.name.split('.').pop()?.toLowerCase()
    if (!ext || !['ofx', 'qfx', 'csv', 'txt'].includes(ext)) {
      setError('Formato não suportado. Use OFX, QFX ou CSV.')
      return
    }
    setError(null)
    setSelectedFile(file)
  }

  const onDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault()
    setDragOver(false)
    const file = e.dataTransfer.files[0]
    if (file) handleFile(file)
  }, [])

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!selectedFile || !accountId) return

    try {
      const result = await upload.mutateAsync({
        file: selectedFile,
        account_id: Number(accountId),
        importer: importerHint === 'auto' ? undefined : importerHint,
      })
      navigate(`/imports/${result.data.id}/preview`)
    } catch (err: any) {
      setError(err?.response?.data?.message ?? 'Erro ao enviar arquivo.')
    }
  }

  return (
    <div className="max-w-xl mx-auto py-10 px-4 space-y-6">
      <div>
        <h1 className="text-2xl font-semibold text-foreground">Importar extrato</h1>
        <p className="text-sm text-muted-foreground mt-1">
          Envie um arquivo OFX ou CSV exportado pelo seu banco.
        </p>
      </div>

      <form onSubmit={onSubmit} className="space-y-5">
        {/* Dropzone */}
        <div
          onClick={() => fileInputRef.current?.click()}
          onDragOver={(e) => { e.preventDefault(); setDragOver(true) }}
          onDragLeave={() => setDragOver(false)}
          onDrop={onDrop}
          className={[
            'border-2 border-dashed rounded-lg p-10 text-center cursor-pointer transition-colors',
            dragOver
              ? 'border-primary bg-primary/5'
              : selectedFile
              ? 'border-green-500 bg-green-500/5'
              : 'border-border hover:border-primary/50',
          ].join(' ')}
        >
          <input
            ref={fileInputRef}
            type="file"
            accept=".ofx,.qfx,.csv,.txt"
            className="hidden"
            onChange={(e) => e.target.files?.[0] && handleFile(e.target.files[0])}
          />
          {selectedFile ? (
            <div className="flex flex-col items-center gap-2">
              <FileUp className="w-8 h-8 text-green-500" />
              <p className="font-medium text-sm">{selectedFile.name}</p>
              <p className="text-xs text-muted-foreground">
                {(selectedFile.size / 1024).toFixed(1)} KB — clique para trocar
              </p>
            </div>
          ) : (
            <div className="flex flex-col items-center gap-2 text-muted-foreground">
              <Upload className="w-8 h-8" />
              <p className="text-sm font-medium">Arraste o arquivo aqui ou clique para selecionar</p>
              <p className="text-xs">OFX, QFX ou CSV — máx. 10 MB</p>
            </div>
          )}
        </div>

        {/* Account select */}
        <div className="space-y-1.5">
          <Label htmlFor="account">Conta de destino</Label>
          <Select value={accountId} onValueChange={setAccountId}>
            <SelectTrigger id="account">
              <SelectValue placeholder="Selecione uma conta..." />
            </SelectTrigger>
            <SelectContent>
              {(accounts ?? []).map((a) => (
                <SelectItem key={a.id} value={String(a.id)}>
                  {a.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* Importer hint */}
        <div className="space-y-1.5">
          <Label htmlFor="importer">Formato (opcional)</Label>
          <Select value={importerHint} onValueChange={setImporterHint}>
            <SelectTrigger id="importer">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {IMPORTERS.map((i) => (
                <SelectItem key={i.id} value={i.id}>
                  {i.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* Error */}
        {error && (
          <div className="flex items-center gap-2 text-sm text-destructive">
            <AlertCircle className="w-4 h-4 shrink-0" />
            {error}
          </div>
        )}

        <Button
          type="submit"
          className="w-full"
          disabled={!selectedFile || !accountId || upload.isPending}
        >
          {upload.isPending ? 'Enviando...' : 'Enviar e processar'}
        </Button>
      </form>
    </div>
  )
}
