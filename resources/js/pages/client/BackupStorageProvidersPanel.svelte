<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import type { BackupStorageProviderItem } from "@/lib/backup-types.js";

  interface KeyRow {
    key: string;
    value: string;
  }

  interface Props {
    providers: BackupStorageProviderItem[];
    onstatus?: (message: string) => void;
  }

  let { providers, onstatus }: Props = $props();

  let editingId = $state<number | null>(null);
  let deletingId = $state<number | null>(null);
  let keyRows = $state<KeyRow[]>([]);

  const form = useForm({
    name: "",
    slug: "",
    keys: {} as Record<string, string>,
  });

  function slugify(value: string): string {
    return value
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }

  function addKeyRow() {
    keyRows = [...keyRows, { key: "", value: "" }];
  }

  function removeKeyRow(index: number) {
    keyRows = keyRows.filter((_, i) => i !== index);
  }

  function buildKeysObject(): Record<string, string> {
    const result: Record<string, string> = {};
    for (const row of keyRows) {
      const k = row.key.trim();
      if (k !== "") {
        result[k] = row.value;
      }
    }
    return result;
  }

  function startEdit(provider: BackupStorageProviderItem) {
    editingId = provider.id;
    form.clearErrors();
    form.name = provider.name;
    form.slug = provider.slug;
    keyRows = Object.entries(provider.keys ?? {}).map(([key, value]) => ({
      key,
      value: String(value ?? ""),
    }));
  }

  function resetForm() {
    editingId = null;
    form.reset();
    form.clearErrors();
    keyRows = [];
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.keys = buildKeysObject();

    const options = {
      preserveScroll: true,
      onSuccess: (page: { props: unknown }) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        resetForm();
      },
    };

    if (editingId !== null) {
      form.put(`/client/backup-storage-providers/${editingId}`, options);
      return;
    }

    form.post("/client/backup-storage-providers", options);
  }

  function handleDelete(id: number) {
    deletingId = id;
    router.delete(`/client/backup-storage-providers/${id}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        if (editingId === id) {
          resetForm();
        }
      },
      onFinish: () => {
        deletingId = null;
      },
    });
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex items-start gap-3">
      <span
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
          <ellipse cx="12" cy="5" rx="9" ry="3"/>
          <path d="M3 5v14a9 3 0 0 0 18 0V5"/>
          <path d="M3 12a9 3 0 0 0 18 0"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">Backup Storage Providers</h2>
        <p class="mt-1 text-sm leading-6 text-muted">
          Define storage provider templates and default configuration keys.
        </p>
      </div>
    </div>
  </div>

  <form
    class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6"
    onsubmit={handleSubmit}
    novalidate
  >
    <div class="flex items-center justify-between gap-2">
      <h3 class="text-sm font-semibold text-ink">
        {editingId !== null ? "Edit Storage Provider" : "Add Storage Provider"}
      </h3>
      {#if editingId !== null}
        <button
          type="button"
          onclick={resetForm}
          class="cursor-pointer text-xs font-medium text-accent hover:underline"
        >
          Cancel edit
        </button>
      {/if}
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
      <div class="grid gap-2">
        <Label for="provider-name">Name</Label>
        <Input
          id="provider-name"
          type="text"
          placeholder="Cloud Storage"
          bind:value={form.name}
          oninput={() => {
            if (editingId === null) {
              form.slug = slugify(form.name);
            }
          }}
          disabled={form.processing}
          aria-invalid={Boolean(form.errors.name)}
        />
        {#if form.errors.name}
          <p class="text-xs text-destructive">{form.errors.name}</p>
        {/if}
      </div>

      <div class="grid gap-2">
        <Label for="provider-slug">Slug</Label>
        <Input
          id="provider-slug"
          type="text"
          placeholder="cloud-storage"
          bind:value={form.slug}
          disabled={form.processing}
          aria-invalid={Boolean(form.errors.slug)}
        />
        {#if form.errors.slug}
          <p class="text-xs text-destructive">{form.errors.slug}</p>
        {/if}
      </div>
    </div>

    <div class="mt-5 border-t border-line pt-4">
      <div class="flex items-center justify-between gap-2">
        <Label>Provider Keys</Label>
        <Button type="button" variant="outline" size="sm" onclick={addKeyRow}>
          Add key
        </Button>
      </div>

      {#if keyRows.length > 0}
        <div class="mt-3 space-y-2">
          {#each keyRows as row, index (index)}
            <div class="grid grid-cols-[1fr_1fr_auto] gap-2">
              <Input type="text" placeholder="Key" bind:value={row.key} />
              <Input type="text" placeholder="Value" bind:value={row.value} />
              <Button type="button" variant="ghost" size="sm" onclick={() => removeKeyRow(index)}>
                Remove
              </Button>
            </div>
          {/each}
        </div>
      {/if}
    </div>

    <div class="mt-5 flex">
      <Button type="submit" disabled={form.processing}>
        {form.processing
          ? "Saving…"
          : editingId !== null
            ? "Update provider"
            : "Create provider"}
      </Button>
    </div>
  </form>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if providers.length === 0}
      <p class="text-sm text-muted">No storage providers configured yet.</p>
    {:else}
      <div class="space-y-3">
        {#each providers as provider (provider.id)}
          <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-line bg-page p-4">
            <div class="min-w-0 space-y-1">
              <p class="text-sm font-semibold text-ink">{provider.name}</p>
              <p class="text-xs text-muted">
                Slug: <span class="font-mono">{provider.slug}</span> &middot; Storages: {provider.storagesCount} &middot; Keys: {Object.keys(provider.keys ?? {}).length}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <Button type="button" variant="outline" size="sm" onclick={() => startEdit(provider)}>
                Edit
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                disabled={deletingId === provider.id}
                onclick={() => handleDelete(provider.id)}
              >
                {deletingId === provider.id ? "Deleting…" : "Delete"}
              </Button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
