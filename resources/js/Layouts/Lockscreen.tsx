import React from "react";
import { Outlet } from "react-router";

export default function Lockscreen() {
    return (
        <div className="flex flex-1 justify-center items-center">
            <Outlet />
        </div>
    )
}