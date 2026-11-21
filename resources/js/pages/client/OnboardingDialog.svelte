<script lang="ts">
  import { router, useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import { PasswordInput } from "@/components/ui/password-input/index.js";

  interface Props {
    open: boolean;
    initialName: string;
    completed?: boolean;
    onclose: () => void;
  }

  let { open, initialName, completed = false, onclose }: Props = $props();

  let step = $state<1 | 2 | 3>(1);
  let skipping = $state(false);
  let passwordSaved = $state(false);

  const form = useForm({
    name: "",
    password: "",
    password_confirmation: "",
  });

  $effect(() => {
    if (open && step === 1 && form.name === "" && initialName !== "") {
      form.name = initialName;
    }
  });

  $effect(() => {
    if (completed) {
      step = 3;
    }
  });

  function handleStepOneNext(event: SubmitEvent) {
    event.preventDefault();
    form.clearErrors();
    if (form.name.trim() === "") {
      form.setError("name", "The name field is required.");
      return;
    }
    step = 2;
  }

  function handleSaveOnboarding(withPassword: boolean) {
    form.clearErrors();
    if (!withPassword) {
      form.password = "";
      form.password_confirmation = "";
    }
    passwordSaved = withPassword && form.password !== "";

    form.post("/onboarding", {
      preserveScroll: true,
      onSuccess: () => {
        step = 3;
      },
    });
  }

  function handleStepTwoSubmit(event: SubmitEvent) {
    event.preventDefault();
    handleSaveOnboarding(form.password !== "");
  }

  function handleSkipAll() {
    skipping = true;
    router.post(
      "/onboarding/skip",
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          skipping = false;
          onclose();
        },
      },
    );
  }
</script>

{#if open}
  <div
    role="dialog"
    aria-modal="true"
    aria-labelledby="onboarding-title"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
  >
    <div class="w-full max-w-md">
      <Card class="shadow-lg">
        <CardHeader>
          <div class="flex items-center justify-between gap-2">
            <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
              Step {step} of 3
            </span>
            {#if step < 3}
              <button
                type="button"
                onclick={handleSkipAll}
                disabled={skipping || form.processing}
                class="cursor-pointer text-xs font-medium text-muted hover:text-ink hover:underline disabled:cursor-not-allowed disabled:opacity-50"
              >
                {skipping ? "Skipping…" : "Skip"}
              </button>
            {/if}
          </div>
          <CardTitle id="onboarding-title">
            {#if step === 1}
              What should we call you?
            {:else if step === 2}
              Create a password
            {:else}
              Welcome to Nova Backups!
            {/if}
          </CardTitle>
          <CardDescription>
            {#if step === 1}
              Personalize your profile with your full name, or keep the default from your email.
            {:else if step === 2}
              Add a password for faster sign-in next time. You can also skip this for now.
            {:else}
              Your profile details have been saved and your account is ready.
            {/if}
          </CardDescription>
        </CardHeader>

        <CardContent>
          {#if step === 1}
            <form class="grid gap-5" onsubmit={handleStepOneNext} novalidate>
              <div class="grid gap-2">
                <Label for="onboarding-name">Full name</Label>
                <Input
                  id="onboarding-name"
                  type="text"
                  placeholder="Your full name"
                  autocomplete="name"
                  bind:value={form.name}
                  aria-invalid={Boolean(form.errors.name)}
                />
                {#if form.errors.name}
                  <p class="text-xs text-destructive">{form.errors.name}</p>
                {/if}
              </div>

              <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="outline" onclick={handleSkipAll} disabled={skipping}>
                  Skip for now
                </Button>
                <Button type="submit" disabled={skipping}>
                  Continue
                </Button>
              </div>
            </form>
          {:else if step === 2}
            <form class="grid gap-5" onsubmit={handleStepTwoSubmit} novalidate>
              <div class="grid gap-2">
                <Label for="onboarding-password">Password</Label>
                <PasswordInput
                  id="onboarding-password"
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
                <Label for="onboarding-password-confirmation">Confirm password</Label>
                <PasswordInput
                  id="onboarding-password-confirmation"
                  placeholder="Repeat your password"
                  autocomplete="new-password"
                  bind:value={form.password_confirmation}
                  aria-invalid={Boolean(form.errors.password_confirmation)}
                />
                {#if form.errors.password_confirmation}
                  <p class="text-xs text-destructive">{form.errors.password_confirmation}</p>
                {/if}
              </div>

              <div class="flex flex-wrap items-center justify-between gap-2">
                <Button
                  type="button"
                  variant="ghost"
                  onclick={() => {
                    form.clearErrors();
                    step = 1;
                  }}
                  disabled={form.processing || skipping}
                >
                  Back
                </Button>
                <div class="flex items-center gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    onclick={() => handleSaveOnboarding(false)}
                    disabled={form.processing || skipping}
                  >
                    Skip password
                  </Button>
                  <Button type="submit" disabled={form.processing || skipping}>
                    {form.processing ? "Saving…" : "Save & continue"}
                  </Button>
                </div>
              </div>
            </form>
          {:else}
            <div class="grid gap-5">
              <div class="flex items-start gap-3 rounded-lg border border-line bg-accent-soft p-4 text-sm text-ink">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 size-5 shrink-0 text-accent">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="m9 12 2 2 4-4"/>
                </svg>
                <div class="space-y-1">
                  <p class="font-medium">All set, {form.name || initialName}!</p>
                  <p class="text-xs leading-relaxed text-muted">
                    {passwordSaved
                      ? "Your full name and password have been saved successfully."
                      : "Your profile has been saved successfully. You can set a password anytime in your Profile settings."}
                  </p>
                </div>
              </div>

              <Button type="button" class="w-full" onclick={onclose}>
                Go to Client Area
              </Button>
            </div>
          {/if}
        </CardContent>
      </Card>
    </div>
  </div>
{/if}
