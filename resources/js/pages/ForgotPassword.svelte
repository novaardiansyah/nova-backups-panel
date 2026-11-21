<script lang="ts">
  import { useForm, Link } from "@inertiajs/svelte";
  import AuthShell from "@/components/auth/auth-shell.svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";

  interface Props {
    status?: string | null;
  }

  let { status = null }: Props = $props();

  let sent = $state(false);

  const form = useForm({
    email: "",
  });

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.post("/forgot-password", {
      onSuccess: () => {
        sent = true;
      },
    });
  }

  function handleReset() {
    sent = false;
    status = null;
  }
</script>

<AuthShell
  title="Reset your password"
  description="Enter your email and we'll send you a link to reset it."
>
  {#if sent || status}
    <div class="grid gap-5">
      <p class="rounded-md bg-accent-soft px-3 py-3 text-sm text-accent">
        If an account exists for <span class="font-medium">{form.email}</span>, a password reset link has been sent.
      </p>
      <Button variant="outline" class="w-full" onclick={handleReset}>
        Send again
      </Button>
      <p class="border-t border-line pt-4 text-center text-sm text-muted">
        Remembered it?
        <Link href="/login" class="cursor-pointer font-medium text-accent hover:underline">Back to sign in</Link>
      </p>
    </div>
  {:else}
    <form class="grid gap-5" onsubmit={handleSubmit}>
      <div class="grid gap-2">
        <Label for="email">Email</Label>
        <Input
          id="email"
          type="email"
          placeholder="you@example.com"
          autocomplete="email"
          bind:value={form.email}
          required
        />
        {#if form.errors.email}
          <p class="text-xs text-destructive">{form.errors.email}</p>
        {/if}
      </div>

      <Button type="submit" class="w-full" disabled={form.processing}>
        {form.processing ? "Sending…" : "Send reset link"}
      </Button>

      <p class="border-t border-line pt-4 text-center text-sm text-muted">
        Remembered it?
        <Link href="/login" class="cursor-pointer font-medium text-accent hover:underline">Back to sign in</Link>
      </p>
    </form>
  {/if}
</AuthShell>
