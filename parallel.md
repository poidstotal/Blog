Speed up R computing with parallel in Unix system (Linux or Mac OS)
================

## Getting started

Like most programming language by default, R processes tasks
sequentially using a single process. But most computer now has multiple
CPU cores each of which can acts like a fully functional CPU. Parallel
computing is taking advantage of as this feature and allows to speed up
as much time as number of CPU cores you have or can spare for a give
task.

Because parallel computing requires some additional work than
sequential, it is not always worth the trouble. My personal suggestion
is to consider parallel computing with tasks require longer that the
time to grab a cup of coffee (let say three to five minutes) and you
need to repeat it more that three time in a sitting(let say an interval
of one to three hours). Otherwise, you can get by for with simple loop
programming.

Parallel computing usually requires three steps. First you need to
define the task to iterate . Second you call task multiple time with
parameters. And third the processing of the result. This is optional but
is usually required because the immediate result from a parallel setup
may come in a list format which is not directly usable. Let go through
each these steps one by one.

## Creating the task function.

Most of the time the task to execute may be a built function for example
get a mean of vector. But it gets somehow challenging when the task you
want to perform is not already built in a single R command. In that case
you need to built your own function. The good news here is that unless
the second step which is platform dependent the function you create here
can be used in either windows or unix platform.

For the purpose of this demonstration we will use a simple but fully
function called my.weekDay. This function returns the date of the
weekday(excluding weekends) that immediately proceeds or follows a given
date.

``` r
# Return the date of the weekday that immediately proceeds or follows a dte
my.weekday <- function(dte, after = TRUE) {
  dte <- as.Date(dte)
  # Get dates before and after dte
  dtes <- seq(dte - 2, dte + 2, by = "days")
  # exclude dates where weekdays is Sunday or Saturday
  dtes <- dtes[!grepl("S(at|un)", weekdays(dtes))]
  # Get the first date greater than dte if after=TRUE
  if (after == TRUE) {
    # Get the first date after dte if after=TRUE
    res <- first(dtes[dtes >= dte])
  } else {
    # Get the last date before dte otherwise
    res <- last(dtes[dtes <= dte])
  }
  # combine and return dte, res and wkd
  res <- data.table(dte, res, wkd = weekdays(res))
  return(res)
}
```

Now that we have our function created we can proceed with the next
steps.

## Second call task multiple time with parameters

Our function has one required input and one optional. Normally, you
would already have a list of inputs to apply for function to. For the
purpose let suppose we need to get the weekday before each date fo the
year 2020. We can build our list of dates using the seq function in R.

``` r
startDte <- as.Date("2000-01-01")
endDte <- as.Date("2020-12-31")
dtes <- seq(startDte,endDte, by="days")
```

### Checking that function works

You need to ensure that this function works in a single call as it
easier to debug and fix error there than it would be in parallel
computing. You may need to test various variants of the inputs to reduce
the chance of error.

``` r
my.weekday(dtes[1])
```

    ##           dte        res    wkd
    ## 1: 2000-01-01 2000-01-03 Monday

``` r
my.weekday(endDte)
```

    ##           dte        res      wkd
    ## 1: 2020-12-31 2020-12-31 Thursday

### Calling the function in a simple loop

Our **dtes** vector contains 7667 dates. For comparison purpose, let see
how long a simple loop would take.

``` r
# simple loop with time measure
start_time <- Sys.time()
dtesRes <- NULL
for (i in 1:length(dtes)){
  res <- my.weekday(dtes[i])
  dtesRes <- rbindlist(list(dtesRes,res),fill = TRUE)
}
end_time <- Sys.time()
end_time - start_time
```

    ## Time difference of 4.376807 secs

``` r
dtesRes
```

    ##              dte        res       wkd
    ##    1: 2000-01-01 2000-01-03    Monday
    ##    2: 2000-01-02 2000-01-03    Monday
    ##    3: 2000-01-03 2000-01-03    Monday
    ##    4: 2000-01-04 2000-01-04   Tuesday
    ##    5: 2000-01-05 2000-01-05 Wednesday
    ##   ---                                
    ## 7667: 2020-12-27 2020-12-28    Monday
    ## 7668: 2020-12-28 2020-12-28    Monday
    ## 7669: 2020-12-29 2020-12-29   Tuesday
    ## 7670: 2020-12-30 2020-12-30 Wednesday
    ## 7671: 2020-12-31 2020-12-31  Thursday

This returns 4.38 seconds which the time it took to run 7667 iterations
of my.weekday. In practice, such a small time would not be worth the
using parallel computing. Now let see how this operation is speed up
with parallel computing.

### Calling the function in a simple loop

Parallel computing in Unix system is performed using mclapply in the
following call. The first argument is the list of items for which we’re
going to apply the function, the second argument is the function to
perform for each item of the list. The number of core to used is
specified with *mc.cores=4*. mclapply also allows to send any argument
require by the function. In our case, we have the **after** argument
which is set to **FALSE**.

``` r
start_time <- Sys.time()
# make the call
dtesRes <- mclapply(dtes,my.weekday,mc.cores=4, after=FALSE)
# process the result
dtesRes <- rbindlist(dtesRes, fill = TRUE)
end_time <- Sys.time()
end_time - start_time
```

    ## Time difference of 1.150678 secs

``` r
dtesRes
```

    ##              dte        res       wkd
    ##    1: 2000-01-01 1999-12-31    Friday
    ##    2: 2000-01-02 1999-12-31    Friday
    ##    3: 2000-01-03 2000-01-03    Monday
    ##    4: 2000-01-04 2000-01-04   Tuesday
    ##    5: 2000-01-05 2000-01-05 Wednesday
    ##   ---                                
    ## 7667: 2020-12-27 2020-12-25    Friday
    ## 7668: 2020-12-28 2020-12-28    Monday
    ## 7669: 2020-12-29 2020-12-29   Tuesday
    ## 7670: 2020-12-30 2020-12-30 Wednesday
    ## 7671: 2020-12-31 2020-12-31  Thursday

It took 1.15 to perform the equivalent of 7667 iterations. Compared to
the previous time usage,the entire process has been speed up by 4 times
which is also about the number of core we used. This is indeed a great
deal, especially in situation where a single call of the function would
take minutes. Nevertheless, in this particular case, parallel computing
maybe not required. As a matter of fact, since the code above is using
**mclapply** which is the parallel version of **lapply**, we could save
noticeable time just by using *lapply* which is an optimized way of
looping.

### call function with lapply

``` r
start_time <- Sys.time()
# make the call
dtesRes <- lapply(dtes,my.weekday,after=FALSE)
# process the result
dtesRes <- rbindlist(dtesRes, fill=TRUE)
end_time <- Sys.time()
end_time - start_time
```

    ## Time difference of 3.213683 secs

``` r
dtesRes
```

    ##              dte        res       wkd
    ##    1: 2000-01-01 1999-12-31    Friday
    ##    2: 2000-01-02 1999-12-31    Friday
    ##    3: 2000-01-03 2000-01-03    Monday
    ##    4: 2000-01-04 2000-01-04   Tuesday
    ##    5: 2000-01-05 2000-01-05 Wednesday
    ##   ---                                
    ## 7667: 2020-12-27 2020-12-25    Friday
    ## 7668: 2020-12-28 2020-12-28    Monday
    ## 7669: 2020-12-29 2020-12-29   Tuesday
    ## 7670: 2020-12-30 2020-12-30 Wednesday
    ## 7671: 2020-12-31 2020-12-31  Thursday

This returns returns 3.21 seconds which although not as fast as in the
parallel setup is less than the time used in **if loop**

## Some final thoughts.

Parallel compute have some cons. These includes for example the use of
extra memory as each process will require it own memory allocation. This
should not be an issue with high computing but low memory task. You
should NOT devote all your core to a give task because the operating
system has many other task ongoing. Personally I don’t allocate more
than half the total number of cores. If you allocate too much of core,
your computer is will likely freeze and may become unusable until the
task is complete.
