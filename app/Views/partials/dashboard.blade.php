<h1 class="text-3xl font-bold mb-4">{{ $title }}</h1>

<section id="chartWrap"
         hx-get="/dashboard/chart"
         hx-trigger="load"
         hx-swap="innerHTML"
         class="card bg-base-100 shadow p-4">
  <div class="skeleton w-full h-40"></div>
</section>

<section class="card bg-base-100 shadow p-4 mt-6"
         hx-get="/dashboard/table"
         hx-trigger="load"
         hx-swap="innerHTML">
  <div class="skeleton w-full h-24"></div>
</section>
