<script lang="ts">
  import { useForm, Link } from "@inertiajs/svelte";
  import AuthShell from "@/components/auth/auth-shell.svelte";
  import GoogleButton from "@/components/auth/google-button.svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { PasswordInput } from "@/components/ui/password-input/index.js";
  import { Toast } from "@/components/ui/toast/index.js";

  let showGoogleToast = $state(false);

  const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    agree: false,
  });

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.post("/register", {
      onFinish: () => form.reset("password", "password_confirmation"),
    });
  }

  function handleGoogle() {
    showGoogleToast = true;
  }
</script>

<AuthShell title="Create your account" description="Start protecting your data with scheduled backups.">
  <form class="grid gap-5" onsubmit={handleSubmit} novalidate>
    <div class="grid gap-2">
      <Label for="full-name">Full name</Label>
      <Input
        id="full-name"
        type="text"
        placeholder="Jane Doe"
        autocomplete="name"
        bind:value={form.name}
        aria-invalid={Boolean(form.errors.name)}
      />
      {#if form.errors.name}
        <p class="text-xs text-destructive">{form.errors.name}</p>
      {/if}
    </div>

    <div class="grid gap-2">
      <Label for="email">Email</Label>
      <Input
        id="email"
        type="email"
        placeholder="you@example.com"
        autocomplete="email"
        bind:value={form.email}
        aria-invalid={Boolean(form.errors.email)}
      />
      {#if form.errors.email}
        <p class="text-xs text-destructive">{form.errors.email}</p>
      {/if}
    </div>

    <div class="grid gap-2">
      <Label for="password">Password</Label>
      <PasswordInput
        id="password"
        placeholder="Create a strong password"
        autocomplete="new-password"
        bind:value={form.password}
        aria-invalid={Boolean(form.errors.password)}
      />
      {#if form.errors.password}
        <p class="text-xs text-destructive">{form.errors.password}</p>
      {/if}
    </div>

    <div class="grid gap-2">
      <Label for="confirm-password">Confirm password</Label>
      <PasswordInput
        id="confirm-password"
        placeholder="Repeat your password"
        autocomplete="new-password"
        bind:value={form.password_confirmation}
        aria-invalid={Boolean(form.errors.password_confirmation)}
      />
      {#if form.errors.password_confirmation}
        <p class="text-xs text-destructive">{form.errors.password_confirmation}</p>
      {/if}
    </div>

    <div class="grid gap-2">
      <label class="flex items-start gap-2 text-sm text-muted">
        <input
          type="checkbox"
          bind:checked={form.agree}
          class="mt-0.5 size-4 rounded border border-line-strong accent-[var(--accent)]"
        />
        <span>
          I agree to the
          <Link href="/terms" class="cursor-pointer font-medium text-accent hover:underline">
            Terms of Service
          </Link>
          and
          <Link href="/privacy" class="cursor-pointer font-medium text-accent hover:underline">
            Privacy Policy
          </Link>.
        </span>
      </label>
      {#if form.errors.agree}
        <p class="text-xs text-destructive">{form.errors.agree}</p>
      {/if}
    </div>

    <Button type="submit" class="w-full" disabled={form.processing}>
      {form.processing ? "Creating account…" : "Create account"}
    </Button>

    <div class="flex justify-center">
      <span class="text-xs font-medium uppercase tracking-wide text-faint">or</span>
    </div>

    <GoogleButton label="Sign up with Google" onclick={handleGoogle} />

    <p class="border-t border-line pt-4 text-center text-sm text-muted">
      Already have an account?
      <Link href="/login" class="cursor-pointer font-medium text-accent hover:underline">Sign in</Link>
    </p>
  </form>
</AuthShell>

<Toast
  open={showGoogleToast}
  title="Feature in development"
  description="Google sign-up is still under development and not available yet. Please create your account using the manual registration form."
  onclose={() => (showGoogleToast = false)}
/>
