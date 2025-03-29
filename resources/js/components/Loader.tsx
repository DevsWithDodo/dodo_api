import Spinner from "@/svg/loader.svg?react";
import React from "react";

export default function Loader({className, ...props}: React.SVGAttributes<SVGSVGElement>) {
    return <Spinner className={`z-10 animate-spin fill-primary ${className}`} {...props} />
}