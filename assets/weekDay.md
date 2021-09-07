
```r
# Return the date of the weekday that immediately proceeds or follows a dte
my.weekday <- function(dte, after=TRUE){
  require(data.table)
  dte <- as.Date(dte)
  # Get dates before and after dte
  dtes <- seq(dte-2,dte+2,by="days")
  # exclude dates where weekdays is Sunday or Saturday
  dtes <- dtes[!grepl("S(at|un)", weekdays(dtes))]
  # Get the first date greater than dte if after=TRUE
  if (after == TRUE){
    # Get the first date after dte if after=TRUE
    dte <- first(dtes[dtes >= dte])
    }else{
    # Get the last date before dte otherwise
    dte <-last(dtes[dtes <= dte])
    }
  # return dte
  return(dte)
}

# Example
# > dte <- "2021-01-02"
# > my.weekday(dte)
# [1] "2021-01-04"
# >  my.weekday("2021-01-01",after=FALSE)
# [1] "2021-01-01"
# >  my.weekday("2021-01-02",after=FALSE)
# [1] "2021-01-01"
```